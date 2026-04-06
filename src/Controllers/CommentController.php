<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\CommentReport;
use App\Models\Manga;
use App\Models\User;
use App\Services\DamNgocRewardService;
use App\Services\UserCommentWaifuPreviewLoader;
use App\Services\UserPointsInAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class CommentController extends Controller
{
    private const ALLOWED_REACTIONS = ['like', 'love', 'care', 'haha', 'wow', 'sad', 'angry'];

    public function __construct(
        private readonly DamNgocRewardService $damNgocRewards,
        private readonly UserPointsInAppNotificationService $pointsInAppNotifications,
        private readonly UserCommentWaifuPreviewLoader $commentWaifuPreview,
    ) {}

    public function index(Request $request, Manga $manga): JsonResponse
    {
        abort_unless($manga->isVisibleTo($request->user()), 404);

        $perPage = 5;

        $paginator = Comment::query()
            ->where('manga_id', $manga->id)
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->with([
                'user:id,name,avatar,current_level,experience_points',
                'user.level',
                'chapter:id,manga_id,title,chapter_number',
                'reactions:id,comment_id,user_id,reaction',
            ])
            ->withCount([
                'replies as replies_count' => fn ($q) => $q->where('is_hidden', false),
            ])
            ->latest('created_at')
            ->paginate($perPage);

        $authId = $request->user()?->id;

        $waifuMap = $this->commentWaifuPreview->loadForUserIds(
            collect($paginator->items())->pluck('user_id')->unique()->all()
        );

        $items = collect($paginator->items())->map(function (Comment $comment) use ($authId, $waifuMap): array {
            return array_merge($this->serializeComment($comment, $authId, $waifuMap), [
                'replies_count' => (int) $comment->replies_count,
            ]);
        })->all();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * Danh sách phản hồi trực tiếp của một bình luận (mọi cấp parent_id = comment).
     */
    public function replies(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->is_hidden) {
            return response()->json(['message' => 'Không tìm thấy.'], 404);
        }

        $manga = Manga::query()->whereKey($comment->manga_id)->first(['id', 'user_id', 'published_at']);
        if (! $manga || ! $manga->isVisibleTo($request->user())) {
            return response()->json(['message' => 'Không tìm thấy.'], 404);
        }

        $perPage = 50;

        $paginator = Comment::query()
            ->where('parent_id', $comment->id)
            ->where('is_hidden', false)
            ->with([
                'user:id,name,avatar,current_level,experience_points',
                'user.level',
                'chapter:id,manga_id,title,chapter_number',
                'reactions:id,comment_id,user_id,reaction',
            ])
            ->withCount([
                'replies as replies_count' => fn ($q) => $q->where('is_hidden', false),
            ])
            ->oldest('created_at')
            ->paginate($perPage);

        $authId = $request->user()?->id;

        $waifuMap = $this->commentWaifuPreview->loadForUserIds(
            collect($paginator->items())->pluck('user_id')->unique()->all()
        );

        $items = collect($paginator->items())->map(function (Comment $reply) use ($authId, $waifuMap): array {
            return array_merge($this->serializeComment($reply, $authId, $waifuMap), [
                'replies_count' => (int) $reply->replies_count,
            ]);
        })->all();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(Request $request, Manga $manga): JsonResponse
    {
        abort_unless($manga->isVisibleTo($request->user()), 404);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'chapter_id' => [
                'nullable',
                'integer',
                Rule::exists('chapters', 'id')->where('manga_id', (int) $manga->id),
            ],
        ]);

        $parentId = isset($validated['parent_id']) ? (int) $validated['parent_id'] : null;
        $chapterId = isset($validated['chapter_id']) ? (int) $validated['chapter_id'] : null;

        if ($parentId !== null) {
            $parent = Comment::query()->find($parentId);
            if (
                ! $parent
                || (int) $parent->manga_id !== (int) $manga->id
                || $parent->is_hidden
            ) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Bình luận gốc không hợp lệ.'],
                ]);
            }
        }

        $payload = DB::transaction(function () use ($request, $manga, $parentId, $chapterId, $validated): array {
            /** @var User $lockedUser */
            $lockedUser = User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

            $comment = Comment::query()->create([
                'user_id' => (int) $lockedUser->id,
                'manga_id' => (int) $manga->id,
                'parent_id' => $parentId,
                'chapter_id' => $chapterId,
                'content' => trim((string) $validated['content']),
            ]);

            $dnBonus = null;
            $applied = $this->damNgocRewards->applyFirstCommentBonusIfEligible($lockedUser);
            if ($applied['awarded']) {
                $dnBonus = [
                    'awarded' => true,
                    'amount' => $applied['amount'],
                    'message' => $applied['message'],
                    'points' => $applied['points'],
                ];
            }
            $lockedUser->save();

            if ($dnBonus !== null) {
                $amt = (int) $dnBonus['amount'];
                $waifuUrl = Route::has('waifu.summon') ? route('waifu.summon') : url('/waifu/summon');
                $this->pointsInAppNotifications->record(
                    $lockedUser,
                    $amt,
                    'Thưởng bình luận.',
                    $amt === 1
                        ? 'Chúc mừng bạn nhận được 1 Dâm Ngọc!'
                        : "Chúc mừng bạn nhận được {$amt} Dâm Ngọc!",
                    $waifuUrl,
                    'Đi Triệu Hồi Waifu',
                );
            }

            $comment->load([
                'user:id,name,avatar,current_level,experience_points',
                'user.level',
                'chapter:id,manga_id,title,chapter_number',
                'reactions:id,comment_id,user_id,reaction',
            ]);

            $authId = (int) $lockedUser->id;

            $waifuMap = $this->commentWaifuPreview->loadForUserIds([(int) $comment->user_id]);

            $out = [
                'ok' => true,
                'id' => (int) $comment->id,
                'comment' => array_merge($this->serializeComment($comment, $authId, $waifuMap), [
                    'replies_count' => 0,
                ]),
            ];
            if ($dnBonus !== null) {
                $out['dn_bonus'] = $dnBonus;
            }

            return $out;
        });

        return response()->json($payload);
    }

    /**
     * Báo cáo bình luận (mỗi user một lần / comment).
     */
    public function report(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->is_hidden) {
            return response()->json(['message' => 'Không tìm thấy.'], 404);
        }

        $manga = Manga::query()->whereKey($comment->manga_id)->first(['id', 'user_id', 'published_at']);
        if (! $manga || ! $manga->isVisibleTo($request->user())) {
            return response()->json(['message' => 'Không tìm thấy.'], 404);
        }

        if ((int) $comment->user_id === (int) $request->user()->id) {
            throw ValidationException::withMessages([
                'content' => ['Bạn không thể báo cáo bình luận của chính mình.'],
            ]);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        if (CommentReport::query()
            ->where('comment_id', (int) $comment->id)
            ->where('user_id', (int) $request->user()->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'content' => ['Bạn đã báo cáo bình luận này rồi.'],
            ]);
        }

        DB::transaction(function () use ($comment, $request, $validated): void {
            CommentReport::query()->create([
                'comment_id' => (int) $comment->id,
                'user_id' => (int) $request->user()->id,
                'content' => trim((string) $validated['content']),
            ]);
            $comment->increment('reports_count');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Đã gửi báo cáo.',
        ]);
    }

    public function react(Request $request, Comment $comment): JsonResponse
    {
        $manga = Manga::query()->whereKey($comment->manga_id)->first(['id', 'user_id', 'published_at']);
        if (! $manga || ! $manga->isVisibleTo($request->user())) {
            return response()->json(['message' => 'Không tìm thấy.'], 404);
        }

        $validated = $request->validate([
            'reaction' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_REACTIONS)],
        ]);

        CommentReaction::query()->updateOrCreate(
            [
                'comment_id' => (int) $comment->id,
                'user_id' => (int) $request->user()->id,
            ],
            [
                'reaction' => (string) $validated['reaction'],
            ]
        );

        $comment->load('reactions:id,comment_id,user_id,reaction');

        $reactionCounts = $comment->reactions
            ->countBy('reaction')
            ->map(fn (int $c): int => $c)
            ->all();

        return response()->json([
            'ok' => true,
            'reaction' => (string) $validated['reaction'],
            'reaction_counts' => $reactionCounts,
            'my_reaction' => (string) $validated['reaction'],
        ]);
    }

    /**
     * Chuẩn hóa payload JSON cho một comment (đã eager load user + reactions).
     *
     * @return array<string, mixed>
     */
    /**
     * @param  array<int, list<array{slug: string, name: string, image_url: string, rarity: int}>>  $topWaifusByUserId
     */
    private function serializeComment(Comment $comment, ?int $authId, array $topWaifusByUserId = []): array
    {
        if (! $comment->relationLoaded('reactions')) {
            $comment->load(['reactions:id,comment_id,user_id,reaction']);
        }
        if (! $comment->relationLoaded('user')) {
            $comment->load(['user:id,name,avatar,current_level,experience_points', 'user.level']);
        }
        if ($comment->chapter_id && ! $comment->relationLoaded('chapter')) {
            $comment->load('chapter:id,manga_id,title,chapter_number');
        }

        $user = $comment->user;
        $userKey = (int) ($user?->getKey() ?? 0);

        $reactionCounts = $comment->reactions
            ->countBy('reaction')
            ->map(fn (int $c): int => $c)
            ->all();

        $myReaction = null;
        if ($authId) {
            $myReaction = $comment->reactions->firstWhere('user_id', (int) $authId)?->reaction;
        }

        return [
            'id' => (int) $comment->id,
            'parent_id' => $comment->parent_id !== null ? (int) $comment->parent_id : null,
            'content' => (string) $comment->content,
            'created_at_human' => $comment->created_at?->diffForHumans() ?? '',
            'chapter_label' => $this->chapterLabel($comment->chapter),
            'reaction_counts' => $reactionCounts,
            'my_reaction' => $myReaction !== null ? (string) $myReaction : null,
            'user' => [
                'id' => $userKey,
                'name' => (string) ($user?->name ?? 'Người dùng'),
                'avatar' => (string) ($user?->avatar ?? ''),
                'level_name' => (string) ($user?->level?->name ?? ''),
                'current_level' => (int) ($user?->current_level ?? 0),
                'badge_src' => asset($user?->level?->image ?? ''),
                'top_waifus' => $topWaifusByUserId[$userKey] ?? [],
            ],
        ];
    }

    /**
     * Nhãn hiển thị chương trên badge (tiêu đề chapter hoặc "Chương {số}").
     */
    private function chapterLabel(?Chapter $chapter): ?string
    {
        if ($chapter === null) {
            return null;
        }

        $title = trim((string) ($chapter->title ?? ''));
        if ($title !== '') {
            return $title;
        }

        $num = (float) $chapter->chapter_number;
        if (abs($num - round($num)) < 0.0001) {
            return 'Chương '.(int) round($num);
        }

        return 'Chương '.rtrim(rtrim(number_format($num, 2, '.', ''), '0'), '.');
    }
}
