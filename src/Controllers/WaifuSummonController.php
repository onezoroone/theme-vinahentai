<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\WaifuSummonMilestoneClaimRequest;
use App\Http\Requests\WaifuSummonPerformRequest;
use App\Models\WaifuSummonLog;
use App\Services\WaifuSummonMilestoneClaimService;
use App\Services\WaifuSummonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * API triệu hồi waifu (trừ điểm, cộng EXP, trả danh sách thẻ).
 */
final class WaifuSummonController extends Controller
{
    private const int REWARDS_HISTORY_PER_PAGE = 8;

    /**
     * Lịch sử triệu hồi (phân trang JSON — không đổi query URL trang).
     */
    public function rewardsHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $page = max(1, (int) $request->query('page', 1));

        if (! Schema::hasTable('waifu_summon_logs')) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => self::REWARDS_HISTORY_PER_PAGE,
                    'total' => 0,
                ],
                'pagination_html' => '',
            ]);
        }

        $paginator = WaifuSummonLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('rolled_at')
            ->orderByDesc('id')
            ->paginate(self::REWARDS_HISTORY_PER_PAGE, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(static function (WaifuSummonLog $row): array {
            $kind = (string) $row->result_kind;
            $label = (string) $row->result_label;
            $expShow = str_replace('+', '', $label);
            $rolledAt = $row->rolled_at;

            return [
                'date' => $rolledAt !== null ? $rolledAt->format('d/m/Y') : '—',
                'time' => $rolledAt !== null ? $rolledAt->format('H:i:s') : '—',
                'result' => $kind === 'exp' ? $expShow : $label,
                'rarity' => ($kind === 'exp' || $row->rarity === null)
                    ? 'EXP'
                    : ((string) (int) $row->rarity).'★',
            ];
        })->values()->all();

        $lastPage = max(1, (int) $paginator->lastPage());
        $currentPage = (int) $paginator->currentPage();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'pagination_html' => $this->renderWaifuRewardsPaginationHtml($currentPage, $lastPage),
        ]);
    }

    /**
     * HTML phân trang client-side — cùng template theme-vinahentai::components.pagination.
     */
    private function renderWaifuRewardsPaginationHtml(int $currentPage, int $lastPage): string
    {
        if ($lastPage <= 1) {
            return '';
        }

        return view('theme-vinahentai::components.pagination', [
            'paginator' => null,
            'current' => $currentPage,
            'last' => $lastPage,
        ])->render();
    }

    public function perform(
        WaifuSummonPerformRequest $request,
        WaifuSummonService $service,
    ): JsonResponse {
        try {
            $payload = $service->perform(
                $request->user(),
                $request->validated('type'),
            );

            return response()->json($payload);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? 'Không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Nhận Dâm Ngọc khi đạt mốc triệu hồi (50 / 100 / 200 / 500).
     */
    public function claimMilestone(
        WaifuSummonMilestoneClaimRequest $request,
        WaifuSummonMilestoneClaimService $service,
    ): JsonResponse {
        try {
            return response()->json(
                $service->claim(
                    $request->user(),
                    (int) $request->validated('milestone'),
                ),
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? 'Không hợp lệ.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
