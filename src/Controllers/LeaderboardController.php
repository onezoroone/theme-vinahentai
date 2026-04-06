<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Artesaos\SEOTools\Facades\SEOMeta;

final class LeaderboardController extends Controller
{
    /**
     * Top tuần / Top tháng.
     *
     * Trả JSON:
     * - data[]: [{rank,title,url,poster,views,follows}]
     */
    public function top(Request $request, string $period): JsonResponse
    {
        $period = strtolower($period);
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(20, max(1, (int) $request->query('per_page', 5)));

        $column = match ($period) {
            'daily' => 'views_day',
            'weekly' => 'views_week',
            'monthly' => 'views_month',
            default => null,
        };

        if ($column === null) {
            abort(404);
        }

        $columnName = $column;
        $offset = ($page - 1) * $perPage;

        // Cache ngắn để giảm query khi user bấm tab/pagination liên tục.
        $cacheTtlSeconds = 60;
        $cacheKey = "leaderboard.top.v2.{$period}.p{$page}.pp{$perPage}";

        $items = Cache::remember($cacheKey, $cacheTtlSeconds, function () use ($columnName, $offset, $perPage): array {
            /** @var Collection<int, Manga> $mangas */
            $mangas = Manga::query()
                ->published()
                ->orderByDesc($columnName)
                ->skip($offset)
                ->take($perPage)
                ->get();

            return $mangas->values()->map(function (Manga $manga, int $index) use ($offset, $columnName): array {
                $rank = $offset + $index + 1;

                $poster = $manga->cover_image;
                if (! is_string($poster) || trim($poster) === '') {
                    /** @phpstan-ignore-next-line */
                    $poster = (string) ($manga->poster_url ?? '');
                }

                return [
                    'rank' => $rank,
                    'title' => (string) $manga->title,
                    'url' => $manga->getUrl(),
                    'poster' => $poster,
                    'views' => (int) ($manga->{$columnName} ?? 0),
                    'follows' => (int) ($manga->total_follows ?? 0),
                ];
            })->all();
        });

        return response()->json([
            'data' => $items,
            'period' => $period,
            'page' => $page,
            'per_page' => $perPage,
        ]);
    }

    public function showManga()
    {
        SEOMeta::setTitle('Bảng xếp hạng truyện | ' . env('APP_NAME'), false)
            ->setDescription('Xem bảng xếp hạng truyện phổ biến nhất tại ' . env('APP_NAME'))
            ->setCanonical(route('leaderboard.manga'));

        return view('theme-vinahentai::leaderboard.manga');
    }

    public function showMember()
    {
        SEOMeta::setTitle('Bảng xếp hạng thành viên | ' . env('APP_NAME'), false)
            ->setDescription('Xem bảng xếp hạng thành viên phổ biến nhất tại ' . env('APP_NAME'))
            ->setCanonical(route('leaderboard.member'));

        $users = User::query()
            ->orderByDesc('current_level')
            ->limit(100)
            ->get();

        return view('theme-vinahentai::leaderboard.member', compact('users'));
    }

    public function showWaifu()
    {
        SEOMeta::setTitle('Bảng xếp hạng Harem | ' . env('APP_NAME'), false)
            ->setDescription('Xem bảng xếp hạng Harem phổ biến nhất tại ' . env('APP_NAME'))
            ->setCanonical(route('leaderboard.waifu'));

        // Chỉ cache mảng scalar (không cache Eloquent): tránh unserialize/JSON làm hỏng cấu trúc → lỗi blade.
        $cachedRows = Cache::remember(
            'leaderboard.waifu.v2',
            60,
            fn (): array => $this->buildWaifuLeaderboardRows(100),
        );
        $entries = $this->hydrateWaifuLeaderboardEntries($cachedRows);

        return view('theme-vinahentai::leaderboard.waifu', compact('entries'));
    }

    /**
     * Hàng BXH thô: user_id + số lượng theo sao (đưa vào cache an toàn).
     *
     * @return list<array{user_id: int, rarity_counts: array<int, int>}>
     */
    private function buildWaifuLeaderboardRows(int $limit): array
    {
        $rows = DB::table('user_waifus as uw')
            ->join('waifus as w', 'w.id', '=', 'uw.waifu_id')
            ->select('uw.user_id')
            ->selectRaw('SUM(CASE WHEN w.rarity = 5 THEN uw.quantity ELSE 0 END) as c5')
            ->selectRaw('SUM(CASE WHEN w.rarity = 4 THEN uw.quantity ELSE 0 END) as c4')
            ->selectRaw('SUM(CASE WHEN w.rarity = 3 THEN uw.quantity ELSE 0 END) as c3')
            ->selectRaw('SUM(CASE WHEN w.rarity = 2 THEN uw.quantity ELSE 0 END) as c2')
            ->selectRaw('SUM(CASE WHEN w.rarity = 1 THEN uw.quantity ELSE 0 END) as c1')
            ->groupBy('uw.user_id')
            ->orderByDesc('c5')
            ->orderByDesc('c4')
            ->orderByDesc('c3')
            ->orderByDesc('c2')
            ->orderByDesc('c1')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        return $rows->map(function (object $row): array {
            return [
                'user_id' => (int) $row->user_id,
                'rarity_counts' => [
                    5 => (int) $row->c5,
                    4 => (int) $row->c4,
                    3 => (int) $row->c3,
                    2 => (int) $row->c2,
                    1 => (int) $row->c1,
                ],
            ];
        })->all();
    }

    /**
     * Nạp User + chuẩn hóa key rarity (sau JSON cache key có thể là string).
     *
     * @param  list<array{user_id: int, rarity_counts: array<int|string, int>}>  $rows
     * @return Collection<int, array{user: User, rarity_counts: array<int, int>, top_waifus: list<array{name: string, image: string, slug: string, rarity: int, quantity: int}>}>
     */
    private function hydrateWaifuLeaderboardEntries(array $rows): Collection
    {
        if ($rows === []) {
            return collect();
        }

        $userIds = array_column($rows, 'user_id');
        $users = User::query()
            ->with('level')
            ->withCount('waifus')
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $topWaifusByUser = $this->topWaifusForUserIds($userIds, 5);

        return collect($rows)
            ->map(function (array $row) use ($users, $topWaifusByUser): ?array {
                $user = $users->get($row['user_id']);
                if ($user === null) {
                    return null;
                }

                $counts = [];
                foreach ($row['rarity_counts'] as $star => $n) {
                    $counts[(int) $star] = (int) $n;
                }

                return [
                    'user' => $user,
                    'rarity_counts' => $counts,
                    'top_waifus' => $topWaifusByUser[$row['user_id']] ?? [],
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * 5 waifu “cao nhất” mỗi user: rarity giảm dần, tie-break quantity giảm dần.
     *
     * @param  list<int>  $userIds
     * @return array<int, list<array{name: string, image: string, slug: string, rarity: int, quantity: int}>>
     */
    private function topWaifusForUserIds(array $userIds, int $limit): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = DB::table('user_waifus as uw')
            ->join('waifus as w', 'w.id', '=', 'uw.waifu_id')
            ->whereIn('uw.user_id', $userIds)
            ->orderBy('uw.user_id')
            ->orderByDesc('w.rarity')
            ->orderByDesc('uw.quantity')
            ->orderBy('w.id')
            ->select(['uw.user_id', 'w.name', 'w.image', 'w.slug', 'w.rarity', 'uw.quantity'])
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $uid = (int) $r->user_id;
            if (! isset($map[$uid])) {
                $map[$uid] = [];
            }
            if (count($map[$uid]) >= $limit) {
                continue;
            }

            $image = $r->image;
            $imageUrl = '';
            if (is_string($image) && str_starts_with($image, 'http')) {
                $imageUrl = $image;
            } elseif (is_string($image) && trim($image) !== '') {
                $imageUrl = asset($image);
            }

            $map[$uid][] = [
                'name' => (string) $r->name,
                'image' => $imageUrl,
                'slug' => (string) $r->slug,
                'rarity' => (int) $r->rarity,
                'quantity' => (int) $r->quantity,
            ];
        }

        return $map;
    }

    public function showTranslator()
    {
        SEOMeta::setTitle('Bảng xếp hạng dịch giả | ' . env('APP_NAME'), false)
            ->setDescription('Xem bảng xếp hạng dịch giả phổ biến nhất tại ' . env('APP_NAME'))
            ->setCanonical(route('leaderboard.translator'));

        return view('theme-vinahentai::leaderboard.translator');
    }
}
