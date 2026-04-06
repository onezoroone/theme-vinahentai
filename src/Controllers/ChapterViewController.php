<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\User;
use App\Services\DamNgocRewardService;
use App\Services\UserPointsInAppNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use JsonException;
use Throwable;

/**
 * Ghi nhận lượt đọc chương — hạn chế buff view bằng API trần.
 *
 * Cơ chế:
 * - Token stateless (mã hóa APP_KEY): không chiếm RAM/cache Redis cho mỗi lượt mở trang.
 * - Tối thiểu thời gian từ lúc phát token (embedded issued_at) đến lúc POST.
 * - Dedupe: cache chỉ 1 key / user hoặc session / chương sau khi đếm thành công (ít hơn rất nhiều so với token theo request).
 * - Dedupe dùng Cache::add để tránh race hai request song song.
 * - Throttle route theo IP.
 */
final class ChapterViewController
{
    public function __construct(
        private readonly DamNgocRewardService $damNgocRewards,
        private readonly UserPointsInAppNotificationService $pointsInAppNotifications,
    ) {}

    private const CACHE_PREFIX_RECORDED = 'chapter_view_recorded:v1:';

    /** Thời gian tối thiểu (giây) từ lúc phát token đến khi chấp nhận POST. */
    private const MIN_SECONDS_SINCE_ISSUE = 30;

    /** Token tối đa sống (phút) — quá hạn thì từ chối. */
    private const TOKEN_TTL_MINUTES = 30;

    /** Không cộng trùng trong TTL này (giờ). */
    private const DEDUPE_TTL_HOURS = 24;

    public function record(Request $request, Chapter $chapter): JsonResponse
    {
        abort_unless($chapter->isVisibleAlongManga($request->user()), 404);

        $validated = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
        ]);

        $token = (string) $validated['token'];
        $parsed = $this->parseViewToken($token);
        if ($parsed === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Token không hợp lệ.',
            ], 422);
        }

        $chapterId = $parsed['chapter_id'];
        $issuedAt = $parsed['issued_at'];

        if ($chapterId !== (int) $chapter->id) {
            return response()->json([
                'ok' => false,
                'message' => 'Token không khớp chương.',
            ], 422);
        }

        $now = now()->getTimestamp();
        $elapsed = $now - $issuedAt;
        if ($elapsed < self::MIN_SECONDS_SINCE_ISSUE) {
            return response()->json([
                'ok' => false,
                'message' => 'Quá sớm — vui lòng đọc thêm.',
            ], 422);
        }

        if ($issuedAt > $now || $elapsed > self::TOKEN_TTL_MINUTES * 60) {
            return response()->json([
                'ok' => false,
                'message' => 'Token đã hết hạn.',
            ], 422);
        }

        $userId = $request->user()?->id;
        $dedupeKey = $userId !== null
            ? self::CACHE_PREFIX_RECORDED.$chapter->id.':user:'.$userId
            : self::CACHE_PREFIX_RECORDED.$chapter->id.':sess:'.(string) $request->session()->getId();

        $dedupeTtl = now()->addHours(self::DEDUPE_TTL_HOURS);
        if (! Cache::add($dedupeKey, 1, $dedupeTtl)) {
            return response()->json([
                'ok' => true,
                'counted' => false,
                'message' => 'Đã ghi nhận lượt đọc trước đó.',
            ]);
        }

        try {
            /** @var Manga $manga */
            $manga = $chapter->manga;
            DB::transaction(function () use ($chapter, $manga): void {
                // Query Builder: không qua Eloquent increment → không ghi updated_at.
                DB::table($chapter->getTable())
                    ->where($chapter->getKeyName(), $chapter->getKey())
                    ->incrementEach([
                        'views_count' => 1,
                        'views_day' => 1,
                        'views_week' => 1,
                        'views_month' => 1,
                    ]);

                DB::table($manga->getTable())
                    ->where($manga->getKeyName(), $manga->getKey())
                    ->incrementEach([
                        'total_views' => 1,
                        'views_day' => 1,
                        'views_week' => 1,
                        'views_month' => 1,
                    ]);
            });
        } catch (Throwable $e) {
            report($e);
            Cache::forget($dedupeKey);

            return response()->json([
                'ok' => false,
                'message' => 'Không thể cập nhật lượt xem.',
            ], 500);
        }

        $chapter->refresh();

        $authUser = $request->user();
        $readDnAwarded = 0;
        $dnToastMessage = null;
        $userPoints = null;

        if ($authUser instanceof User) {
            $readDnAwarded = $this->damNgocRewards->randomReadBonusAmount();
            if ($readDnAwarded > 0) {
                $authUser->increment('points', $readDnAwarded);
                $authUser->refresh();
                $dnToastMessage = $readDnAwarded === 1
                    ? 'Bạn nhận +1 Dâm Ngọc nhờ đọc truyện!'
                    : "Bạn nhận +{$readDnAwarded} Dâm Ngọc nhờ đọc truyện!";
                $waifuUrl = Route::has('waifu.summon') ? route('waifu.summon') : url('/waifu/summon');
                $this->pointsInAppNotifications->record(
                    $authUser,
                    $readDnAwarded,
                    'Thưởng đọc truyện.',
                    $readDnAwarded === 1
                        ? 'Chúc mừng bạn nhận được 1 Dâm Ngọc!'
                        : "Chúc mừng bạn nhận được {$readDnAwarded} Dâm Ngọc!",
                    $waifuUrl,
                    'Đi Triệu Hồi Waifu',
                );
            }
            $userPoints = (int) $authUser->points;
        }

        return response()->json([
            'ok' => true,
            'counted' => true,
            'views_count' => (int) $chapter->views_count,
            'read_dn_awarded' => $readDnAwarded,
            'dn_toast_message' => $dnToastMessage,
            'user_points' => $userPoints,
        ]);
    }

    /**
     * @return array{chapter_id: int, issued_at: int}|null
     */
    private function parseViewToken(string $token): ?array
    {
        try {
            $json = Crypt::decryptString($token);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        $cid = (int) ($data['c'] ?? 0);
        $iat = (int) ($data['i'] ?? 0);
        if ($cid < 1 || $iat < 1) {
            return null;
        }

        return [
            'chapter_id' => $cid,
            'issued_at' => $iat,
        ];
    }

    /**
     * Token mang theo payload đã mã hóa — không lưu cache phía server.
     *
     * @throws JsonException
     */
    public static function issueTokenForChapter(Chapter $chapter): string
    {
        $payload = json_encode([
            'c' => (int) $chapter->id,
            'i' => now()->getTimestamp(),
        ], JSON_THROW_ON_ERROR);

        return Crypt::encryptString($payload);
    }
}
