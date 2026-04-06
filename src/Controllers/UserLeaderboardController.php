<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UserLeaderboardController extends Controller
{
    public function top(Request $request, string $period): JsonResponse
    {
        $period = strtolower($period);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 5;

        $column = match ($period) {
            'weekly' => 'views_week',
            'monthly' => 'views_month',
            'all-time' => 'total_views',
            default => null,
        };

        if ($column === null) {
            abort(404);
        }

        $cacheTtlSeconds = 60;
        $cacheKey = "leaderboard.users.top.v2.{$period}.page.{$page}";

        $items = Cache::remember($cacheKey, $cacheTtlSeconds, function () use ($column, $page, $perPage): array {
            $offset = ($page - 1) * $perPage;

            /** @var EloquentCollection<int, object> $rows */
            $rows = Manga::query()
                ->published()
                ->select([
                    'user_id',
                    DB::raw("SUM({$column}) as views_sum"),
                    DB::raw('SUM(total_follows) as follows_sum'),
                ])
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderByDesc('views_sum')
                ->offset($offset)
                ->limit($perPage)
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->values()->all();
            if ($userIds === []) {
                return [];
            }

            $usersById = User::query()
                ->whereIn('id', $userIds)
                ->with('level')
                ->get()
                ->keyBy('id');

            $result = [];
            foreach ($rows as $index => $row) {
                $user = $usersById->get($row->user_id);
                $rank = $offset + (int) $index + 1;
                $title = $user?->name !== null
                    ? (string) $user->name
                    : ('Người dùng #'.$row->user_id);
                $avatar = $user?->avatar ?? '';

                $result[] = [
                    'rank' => $rank,
                    'title' => $title,
                    'url' => '/profile/'.$row->user_id,
                    'avatar' => (string) $avatar,
                    'views' => (int) ($row->views_sum ?? 0),
                    'points' => (int) ($user->points ?? 0),
                ];
            }

            return $result;
        });

        return response()->json([
            'data' => $items,
            'period' => $period,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }
}
