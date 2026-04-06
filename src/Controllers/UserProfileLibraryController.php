<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Manga;
use App\Models\ReadingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * API tab profile: thư viện (theo dõi, lịch sử), bình luận đã đăng, dịch giả/tác giả (placeholder).
 */
final class UserProfileLibraryController extends Controller
{
    public function followedMangas(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request);
        $page = $this->resolvePage($request);
        $ids = $user->followedMangaIds();

        if ($ids === []) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $perPage,
                    'total' => 0,
                ],
            ]);
        }

        $mangas = Manga::query()
            ->whereIn('id', $ids)
            ->where(function ($q) use ($user): void {
                $q->whereNotNull('published_at')
                    ->orWhere('user_id', $user->id);
            })
            ->get(['id', 'title', 'slug', 'cover_image', 'latest_chapter_number', 'total_views']);

        $byId = $mangas->keyBy('id');
        $ordered = collect($ids)
            ->map(fn (int $id) => $byId->get($id))
            ->filter()
            ->values();

        $allItems = $ordered->map(function (Manga $manga): array {
            $chap = $manga->latest_chapter_number;

            return [
                'id' => $manga->id,
                'title' => (string) $manga->title,
                'slug' => (string) $manga->slug,
                'cover_image' => $manga->cover_image,
                'url' => $manga->getUrl(),
                'latest_chapter_label' => $this->formatChapterNumberLabel($chap),
                'total_views' => (int) ($manga->total_views ?? 0),
            ];
        })->values();

        $total = $allItems->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;
        $data = $allItems->slice($offset, $perPage)->values()->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Mỗi manga một dòng: chương đọc gần nhất (theo read_at).
     */
    public function readingHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request);

        $sub = ReadingHistory::query()
            ->selectRaw('manga_id, MAX(read_at) as max_read')
            ->where('user_id', $user->id)
            ->groupBy('manga_id');

        $base = ReadingHistory::query()
            ->joinSub($sub, 't', function ($join): void {
                $join->on('reading_histories.manga_id', '=', 't.manga_id')
                    ->on('reading_histories.read_at', '=', 't.max_read');
            })
            ->where('reading_histories.user_id', $user->id)
            ->whereHas('manga', function ($q) use ($user): void {
                $q->where(function ($w) use ($user): void {
                    $w->whereNotNull('published_at')
                        ->orWhere('user_id', $user->id);
                });
            })
            ->whereHas('chapter')
            ->with([
                'manga' => fn ($q) => $q->select(['id', 'title', 'slug', 'cover_image', 'total_views', 'published_at', 'user_id']),
                'chapter' => fn ($q) => $q->select(['id', 'manga_id', 'slug', 'chapter_number', 'title', 'pages_count']),
            ])
            ->orderByDesc('reading_histories.read_at');

        /** @var LengthAwarePaginator<int, ReadingHistory> $page */
        $page = $base->paginate($perPage);

        $data = collect($page->items())->map(function (ReadingHistory $row): array {
            $manga = $row->manga;
            $chapter = $row->chapter;
            $chapterUrl = null;
            if ($chapter !== null && $manga !== null) {
                $chapterUrl = $chapter->setRelation('manga', $manga)->getUrl();
            }

            return [
                'read_at' => $row->read_at?->toIso8601String(),
                'last_read_page' => (int) $row->last_read_page,
                'manga' => $manga === null ? null : [
                    'id' => $manga->id,
                    'title' => (string) $manga->title,
                    'slug' => (string) $manga->slug,
                    'cover_image' => $manga->cover_image,
                    'url' => $manga->getUrl(),
                    'total_views' => (int) ($manga->total_views ?? 0),
                ],
                'chapter' => $chapter === null ? null : [
                    'number_label' => $this->formatChapterNumberLabel($chapter->chapter_number),
                    'title' => (string) ($chapter->title ?? ''),
                    'url' => $chapterUrl,
                    'pages_count' => (int) ($chapter->pages_count ?? 0),
                ],
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * Bình luận của user đang đăng nhập (manga còn xem được), phân trang.
     */
    public function myComments(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request);

        $base = Comment::query()
            ->where('user_id', $user->id)
            ->where('is_hidden', false)
            ->whereHas('manga', function ($q) use ($user): void {
                $q->where(function ($w) use ($user): void {
                    $w->whereNotNull('published_at')
                        ->orWhere('user_id', $user->id);
                });
            })
            ->with([
                'manga' => fn ($q) => $q->select(['id', 'title', 'slug', 'cover_image', 'published_at', 'user_id']),
            ])
            ->latest('created_at');

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Comment> $page */
        $page = $base->paginate($perPage);

        $data = collect($page->items())->map(function (Comment $comment): array {
            $manga = $comment->manga;
            $plain = preg_replace('/\s+/u', ' ', trim(strip_tags((string) $comment->content)));
            $preview = Str::limit($plain, 160, '…');

            return [
                'id' => $comment->id,
                'content_preview' => $preview,
                'posted_label' => $comment->created_at?->locale('vi')->diffForHumans() ?? '',
                'manga' => $manga === null ? null : [
                    'id' => $manga->id,
                    'title' => (string) $manga->title,
                    'slug' => (string) $manga->slug,
                    'cover_image' => $manga->cover_image,
                    'url' => $manga->getUrl(),
                ],
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * Chưa có lưu theo dõi dịch giả — trả mảng rỗng (tab vẫn gọi API).
     */
    public function followedTranslators(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    /**
     * Chưa có lưu theo dõi tác giả — trả mảng rỗng.
     */
    public function followedAuthors(): JsonResponse
    {
        return response()->json(['data' => []]);
    }

    private function resolvePerPage(Request $request, int $default = 10): int
    {
        return min(50, max(1, (int) $request->query('per_page', $default)));
    }

    private function resolvePage(Request $request): int
    {
        return max(1, (int) $request->query('page', 1));
    }

    private function formatChapterNumberLabel(mixed $chapterNumber): string
    {
        if (! is_numeric($chapterNumber)) {
            return (string) $chapterNumber;
        }

        $n = (float) $chapterNumber;

        return abs($n - round($n)) < 0.001
            ? (string) (int) round($n)
            : rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
    }
}
