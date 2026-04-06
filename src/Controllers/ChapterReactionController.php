<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Models\Chapter;
use App\Models\User;
use App\Services\DamNgocRewardService;
use App\Services\UserPointsInAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Throwable;

final class ChapterReactionController
{
    public function __construct(
        private readonly DamNgocRewardService $damNgocRewards,
        private readonly UserPointsInAppNotificationService $pointsInAppNotifications,
    ) {}

    public function status(Request $request, Chapter $chapter): JsonResponse
    {
        abort_unless($chapter->isVisibleAlongManga($request->user()), 404);

        $user = $request->user();
        $chapterId = (int) $chapter->id;

        $likedIds = $this->normalizeIds($user?->liked_chapter_ids);
        $dislikedIds = $this->normalizeIds($user?->disliked_chapter_ids);

        return response()->json([
            'liked' => in_array($chapterId, $likedIds, true),
            'disliked' => in_array($chapterId, $dislikedIds, true),
            'like_count' => (int) $chapter->like_count,
            'dislike_count' => (int) $chapter->dislike_count,
        ]);
    }

    public function react(Request $request, Chapter $chapter): JsonResponse
    {
        abort_unless($chapter->isVisibleAlongManga($request->user()), 404);

        $validated = $request->validate([
            'reaction' => ['required', Rule::in(['like', 'dislike'])],
        ]);

        $chapterId = (int) $chapter->id;
        $reaction = (string) $validated['reaction'];

        try {
            return DB::transaction(function () use ($request, $chapter, $chapterId, $reaction): JsonResponse {
                /** @var User $user */
                $user = User::query()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();

                $chapter = $chapter->fresh();
                if (! $chapter) {
                    return response()->json(['message' => 'Không tìm thấy.'], 404);
                }

                $likedIds = $this->normalizeIds($user->liked_chapter_ids);
                $dislikedIds = $this->normalizeIds($user->disliked_chapter_ids);

                $hasLiked = in_array($chapterId, $likedIds, true);
                $hasDisliked = in_array($chapterId, $dislikedIds, true);

                $dnBonus = null;

                if ($reaction === 'like') {
                    if ($hasLiked) {
                        // Toggle bỏ like.
                        $likedIds = array_values(array_diff($likedIds, [$chapterId]));
                        if ((int) $chapter->like_count > 0) {
                            $chapter->decrement('like_count', 1);
                        } else {
                            // Tránh underflow với cột unsigned.
                            $chapter->like_count = 0;
                        }
                    } else {
                        // Gắn like: nếu đang dislike thì gỡ dislike.
                        if ($hasDisliked) {
                            $dislikedIds = array_values(array_diff($dislikedIds, [$chapterId]));
                            if ((int) $chapter->dislike_count > 0) {
                                $chapter->decrement('dislike_count', 1);
                            } else {
                                $chapter->dislike_count = 0;
                            }
                        }
                        if (! $hasLiked) {
                            $likedIds[] = $chapterId;
                        }
                        $chapter->increment('like_count', 1);

                        $applied = $this->damNgocRewards->applyFirstChapterLikeBonusIfEligible($user);
                        if ($applied['awarded']) {
                            $dnBonus = [
                                'awarded' => true,
                                'amount' => $applied['amount'],
                                'message' => $applied['message'],
                                'points' => $applied['points'],
                            ];
                        }
                    }
                }

                if ($reaction === 'dislike') {
                    if ($hasDisliked) {
                        // Toggle bỏ dislike.
                        $dislikedIds = array_values(array_diff($dislikedIds, [$chapterId]));
                        if ((int) $chapter->dislike_count > 0) {
                            $chapter->decrement('dislike_count', 1);
                        } else {
                            $chapter->dislike_count = 0;
                        }
                    } else {
                        // Gắn dislike: nếu đang like thì gỡ like.
                        if ($hasLiked) {
                            $likedIds = array_values(array_diff($likedIds, [$chapterId]));
                            if ((int) $chapter->like_count > 0) {
                                $chapter->decrement('like_count', 1);
                            } else {
                                $chapter->like_count = 0;
                            }
                        }
                        if (! $hasDisliked) {
                            $dislikedIds[] = $chapterId;
                        }
                        $chapter->increment('dislike_count', 1);
                    }
                }

                $user->liked_chapter_ids = array_values(array_unique(array_map('intval', $likedIds)));
                $user->disliked_chapter_ids = array_values(array_unique(array_map('intval', $dislikedIds)));
                $user->save();

                if ($dnBonus !== null) {
                    $amt = (int) $dnBonus['amount'];
                    $waifuUrl = Route::has('waifu.summon') ? route('waifu.summon') : url('/waifu/summon');
                    $this->pointsInAppNotifications->record(
                        $user,
                        $amt,
                        'Thưởng like chương.',
                        $amt === 1
                            ? 'Chúc mừng bạn nhận được 1 Dâm Ngọc!'
                            : "Chúc mừng bạn nhận được {$amt} Dâm Ngọc!",
                        $waifuUrl,
                        'Đi Triệu Hồi Waifu',
                    );
                }

                $chapter->refresh();

                $payload = [
                    'liked' => in_array($chapterId, $this->normalizeIds($user->liked_chapter_ids), true),
                    'disliked' => in_array($chapterId, $this->normalizeIds($user->disliked_chapter_ids), true),
                    'like_count' => (int) $chapter->like_count,
                    'dislike_count' => (int) $chapter->dislike_count,
                ];

                if ($dnBonus !== null) {
                    $payload['dn_bonus'] = $dnBonus;
                }

                return response()->json($payload);
            });
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Không thể xử lý phản ứng.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Normalize danh sách ids từ JSON (có thể null).
     *
     * @param mixed $raw
     * @return int[]
     */
    private function normalizeIds(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $id) {
            if (is_numeric($id)) {
                $out[] = (int) $id;
            }
        }

        return array_values(array_unique($out));
    }
}

