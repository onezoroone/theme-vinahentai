<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Author;
use App\Models\User;
use App\Models\UserWaifu;
use App\Models\Waifu;
use App\Models\Translator;
use App\Services\HomepageSectionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\Chapter;
use App\Models\ShopItem;
use App\Models\WaifuSummonLog;
use App\Models\WaifuSummonMilestoneClaim;
use App\Services\LevelBreakthroughService;
use App\WaifuSummon\WaifuSummonConfig;
use Nqt\ThemeVinahentai\Controllers\ChapterViewController;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ThemeVinahentaiController extends Controller
{
    protected string $prefixTheme = 'theme-vinahentai';

    public function showHome()
    {
        $rows = Cache::remember('hot_mangas_hydrate_v5', 3600, function (): array {
            return Manga::query()
                ->orderBy('total_views', 'desc')
                ->whereNotNull('published_at')
                ->limit(12)
                ->get()
                ->map(fn (Manga $manga) => $manga->getAttributes())
                ->all();
        });

        $hotMangas = Manga::hydrate($rows);
        $hotMangas->load('genres');

        $homeSections = app(HomepageSectionService::class)->resolveSections(
            (string) active_theme_config('home_sections', '')
        );

        $commentIds = Cache::remember('new_comments_ids_v1', 600, function (): array {
            return Comment::query()
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->pluck('id')
                ->all();
        });

        $newComments = $commentIds === []
            ? collect()
            : Comment::query()
                ->with(['user', 'manga'])
                ->whereIn('id', $commentIds)
                ->get()
                ->sortBy(fn (Comment $comment): int => array_search($comment->id, $commentIds, true) ?: 0)
                ->values();

        $usersMostExperience = User::query()
            ->orderBy('current_level', 'desc')
            ->limit(10)
            ->get();

        return view($this->prefixTheme.'::index', compact('hotMangas', 'homeSections', 'newComments', 'usersMostExperience'));
    }

    public function showManga(string $manga)
    {
        $manga = Manga::query()
            ->where('slug', $manga)
            ->withSum('chapters', 'like_count')
            ->first();
        if (! $manga) {
            abort(404);
        }
        if (! $manga->isVisibleTo(Auth::user())) {
            abort(404);
        }
        $manga->generateSeoTags();

        $authorMangas = Manga::query()
            ->published()
            ->where('user_id', $manga->user_id)
            ->where('id', '!=', $manga->id)
            ->orderBy('total_views', 'desc')
            ->limit(4)
            ->get();

        $relatedMangas = Manga::query()
            ->published()
            ->whereHas('genres', fn ($query) => $query->whereIn('genres.id', $manga->genres->pluck('id')))
            ->orderBy('total_views', 'desc')
            ->limit(5)
            ->get();

        return view($this->prefixTheme.'::manga', compact('manga', 'authorMangas', 'relatedMangas'));
    }

    public function showChapter(string $manga, string $chapter)
    {
        $manga = Manga::where('slug', $manga)->first();
        if (! $manga) {
            abort(404);
        }
        if (! $manga->isVisibleTo(Auth::user())) {
            abort(404);
        }
        $chapter = Chapter::where('slug', $chapter)->where('manga_id', $manga->id)->first();
        if (! $chapter) {
            abort(404);
        }

        $chapter->generateSeoTags();

        $prevChapter = $chapter->getPreviousChapter();

        $nextChapter = $chapter->getNextChapter();

        $relatedMangas = Manga::query()
            ->published()
            ->with('genres')
            ->whereHas('genres', fn ($query) => $query->whereIn('genres.id', $manga->genres->pluck('id')))
            ->orderBy('total_views', 'desc')
            ->limit(10)
            ->get();

        $chapterViewToken = ChapterViewController::issueTokenForChapter($chapter);

        return view($this->prefixTheme.'::chapter', [
            'manga' => $manga,
            'chapter' => $chapter,
            'prevChapter' => $prevChapter,
            'nextChapter' => $nextChapter,
            'relatedMangas' => $relatedMangas,
            'chapterViewToken' => $chapterViewToken,
        ]);
    }

    public function showGenre(Request $request, string $genre)
    {
        $genreModel = Genre::query()->where('slug', $genre)->first();
        if (! $genreModel) {
            abort(404);
        }

        $sort = (string) $request->query('sort', 'new');
        $allowedSorts = ['new', 'old', 'views', 'rating', 'completed'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'new';
        }

        $genreModel->generateSeoTags();

        $query = Manga::query()
            ->published()
            ->with('genres')
            ->whereHas('genres', fn ($q) => $q->where('genres.id', $genreModel->id));

        match ($sort) {
            'old' => $query->oldest('updated_at'),
            'views' => $query->orderByDesc('total_views')->orderByDesc('updated_at'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count')->orderByDesc('updated_at'),
            'completed' => $query->where('status', 'completed')->latest('updated_at'),
            default => $query->latest('updated_at'),
        };

        $mangas = $query->paginate(40)->withQueryString();

        $sortLabels = [
            'new' => 'Mới cập nhật',
            'old' => 'Cũ nhất',
            'views' => 'Đọc nhiều',
            'rating' => 'Đánh giá cao',
            'completed' => 'Đã hoàn thành',
        ];

        $preserveQuery = $request->except(['sort', 'page']);
        $sortOptions = [];
        foreach ($sortLabels as $key => $label) {
            $q = array_merge($preserveQuery, ['page' => 1]);
            if ($key === 'new') {
                unset($q['sort']);
            } else {
                $q['sort'] = $key;
            }
            $sortOptions[] = [
                'key' => $key,
                'label' => $label,
                'url' => route('genres.show', ['genre' => $genreModel->slug]).'?'.http_build_query($q),
            ];
        }

        return view($this->prefixTheme.'::tax', [
            'sectionName' => $genreModel->name,
            'sectionItems' => $mangas,
            'type' => 'genre',
            'sectionDescription' => $genreModel->description ?? 'Truyện thuộc thể loại ' . $genreModel->name,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'currentSortLabel' => $sortLabels[$sort] ?? $sortLabels['new'],
        ]);
    }

    public function showAuthor(Request $request, string $author)
    {
        $authorModel = Author::query()->where('slug', $author)->first();
        if (! $authorModel) {
            abort(404);
        }

        $sort = (string) $request->query('sort', 'new');
        $allowedSorts = ['new', 'old', 'views', 'rating', 'completed'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'new';
        }

        $authorModel->generateSeoTags();

        $query = Manga::query()
            ->published()
            ->with('genres')
            ->whereHas('authors', fn ($q) => $q->where('authors.id', $authorModel->id));

        match ($sort) {
            'old' => $query->oldest('updated_at'),
            'views' => $query->orderByDesc('total_views')->orderByDesc('updated_at'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count')->orderByDesc('updated_at'),
            'completed' => $query->where('status', 'completed')->latest('updated_at'),
            default => $query->latest('updated_at'),
        };

        $mangas = $query->paginate(40)->withQueryString();

        $sortLabels = [
            'new' => 'Mới cập nhật',
            'old' => 'Cũ nhất',
            'views' => 'Đọc nhiều',
            'rating' => 'Đánh giá cao',
            'completed' => 'Đã hoàn thành',
        ];

        $preserveQuery = $request->except(['sort', 'page']);
        $sortOptions = [];
        foreach ($sortLabels as $key => $label) {
            $q = array_merge($preserveQuery, ['page' => 1]);
            if ($key === 'new') {
                unset($q['sort']);
            } else {
                $q['sort'] = $key;
            }
            $sortOptions[] = [
                'key' => $key,
                'label' => $label,
                'url' => route('authors.show', ['author' => $authorModel->slug]).'?'.http_build_query($q),
            ];
        }

        return view($this->prefixTheme.'::tax', [
            'sectionName' => $authorModel->name,
            'sectionItems' => $mangas,
            'type' => 'author',
            'sectionDescription' => $authorModel->bio ?? 'Truyện của tác giả ' . $authorModel->name,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'currentSortLabel' => $sortLabels[$sort] ?? $sortLabels['new'],
        ]);
    }

    public function showTranslator(Request $request, string $translator)
    {
        $translatorModel = Translator::query()->where('slug', $translator)->first();
        if (! $translatorModel) {
            abort(404);
        }

        $sort = (string) $request->query('sort', 'new');
        $allowedSorts = ['new', 'old', 'views', 'rating', 'completed'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'new';
        }

        $translatorModel->generateSeoTags();

        $query = Manga::query()
            ->published()
            ->with('genres')
            ->whereHas('translators', fn ($q) => $q->where('translators.id', $translatorModel->id));

        match ($sort) {
            'old' => $query->oldest('updated_at'),
            'views' => $query->orderByDesc('total_views')->orderByDesc('updated_at'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count')->orderByDesc('updated_at'),
            'completed' => $query->where('status', 'completed')->latest('updated_at'),
            default => $query->latest('updated_at'),
        };

        $mangas = $query->paginate(40)->withQueryString();

        $sortLabels = [
            'new' => 'Mới cập nhật',
            'old' => 'Cũ nhất',
            'views' => 'Đọc nhiều',
            'rating' => 'Đánh giá cao',
            'completed' => 'Đã hoàn thành',
        ];

        $preserveQuery = $request->except(['sort', 'page']);
        $sortOptions = [];
        foreach ($sortLabels as $key => $label) {
            $q = array_merge($preserveQuery, ['page' => 1]);
            if ($key === 'new') {
                unset($q['sort']);
            } else {
                $q['sort'] = $key;
            }
            $sortOptions[] = [
                'key' => $key,
                'label' => $label,
                'url' => route('translators.show', ['translator' => $translatorModel->slug]).'?'.http_build_query($q),
            ];
        }

        return view($this->prefixTheme.'::tax', [
            'sectionName' => $translatorModel->name,
            'sectionItems' => $mangas,
            'type' => 'translator',
            'sectionDescription' => $translatorModel->bio ?? 'Truyện của dịch giả ' . $translatorModel->name,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'currentSortLabel' => $sortLabels[$sort] ?? $sortLabels['new'],
        ]);
    }

    /**
     * Trang tìm kiếm đơn giản (?q=).
     */
    public function showSearch(Request $request)
    {
        $q = $request->query('q', '');
        $q = is_string($q) ? trim($q) : '';
        if (mb_strlen($q) > 500) {
            $q = mb_substr($q, 0, 500);
        }

        SEOMeta::setTitle('Kết quả tìm kiếm: ' . $q . ' | ' . env('APP_NAME'), false)
            ->setDescription('Kết quả tìm kiếm: ' . $q . ' trên ' . env('APP_NAME'))
            ->setCanonical(route('search', ['q' => $q]));

        $slugQ = Str::slug($q);
        $mangas = Manga::query()
            ->published()
            ->with('genres')
            ->where(function (Builder $w) use ($q, $slugQ): void {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhere('alternative_title', 'like', '%'.$q.'%')
                    ->orWhere('slug', 'like', '%'.$slugQ.'%');
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view($this->prefixTheme.'::search', [
            'q' => $q,
            'mangas' => $mangas,
        ]);
    }

    /**
     * Tìm kiếm nâng cao — ?apply=1&q=...&status=...&includeGenres=&excludeGenres=&sort=new&page=1
     */
    public function showSearchAdvanced(Request $request)
    {
        $q = $request->query('q', '');
        $q = is_string($q) ? trim($q) : '';
        if (mb_strlen($q) > 500) {
            $q = mb_substr($q, 0, 500);
        }

        $selectedIncludeSlugs = $this->parseCommaSeparatedQueryParam($request->query('includeGenres'));
        $selectedExcludeSlugs = $this->parseCommaSeparatedQueryParam($request->query('excludeGenres'));
        $selectedStatuses = $this->parseCommaSeparatedQueryParam($request->query('status'));

        $sort = (string) $request->query('sort', 'new');
        $allowedSorts = ['new', 'old', 'views', 'rating', 'completed'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'new';
        }

        $hasActiveSearch = $request->query('apply') === '1'
            || $q !== ''
            || $selectedIncludeSlugs !== []
            || $selectedExcludeSlugs !== []
            || $selectedStatuses !== [];

        SEOMeta::setTitle('Tìm kiếm nâng cao | ' . env('APP_NAME'), false)
            ->setDescription('Tìm kiếm nâng cao trên ' . env('APP_NAME'))
            ->setCanonical(route('search.advanced', ['q' => $q]));

        $sortLabels = [
            'new' => 'Mới cập nhật',
            'old' => 'Cũ nhất',
            'views' => 'Đọc nhiều',
            'rating' => 'Đánh giá cao',
            'completed' => 'Đã hoàn thành',
        ];

        $sortOptions = $this->buildAdvancedSearchSortOptions(
            $q,
            $selectedIncludeSlugs,
            $selectedExcludeSlugs,
            $selectedStatuses,
            $sortLabels,
        );

        if (! $hasActiveSearch) {
            $mangas = new LengthAwarePaginator([], 0, 40, max(1, (int) $request->query('page', 1)), [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        } else {
            $query = Manga::query()->published()->with('genres');
            $this->applyAdvancedSearchMangaFilters($query, $q, $selectedIncludeSlugs, $selectedExcludeSlugs, $selectedStatuses);
            $this->applyAdvancedSearchSort($query, $sort);
            $mangas = $query->paginate(40)->withQueryString();
        }

        return view($this->prefixTheme.'::search-advanced', [
            'q' => $q,
            'genresByLetter' => $this->genresGroupedByLetterForAdvancedSearch(),
            'selectedIncludeSlugs' => $selectedIncludeSlugs,
            'selectedExcludeSlugs' => $selectedExcludeSlugs,
            'selectedStatuses' => $selectedStatuses,
            'mangas' => $mangas,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'currentSortLabel' => $sortLabels[$sort] ?? $sortLabels['new'],
            'hasActiveSearch' => $hasActiveSearch,
        ]);
    }

    /**
     * @param  list<string>  $includeSlugs
     * @param  list<string>  $excludeSlugs
     * @param  list<string>  $statusTokens
     */
    private function applyAdvancedSearchMangaFilters(
        Builder $query,
        string $q,
        array $includeSlugs,
        array $excludeSlugs,
        array $statusTokens,
    ): void {
        if ($q !== '') {
            $slugQ = Str::slug($q);
            $query->where(function (Builder $w) use ($q, $slugQ): void {
                $w->where('title', 'like', '%'.$q.'%')
                    ->orWhere('alternative_title', 'like', '%'.$q.'%')
                    ->orWhere('slug', 'like', '%'.$slugQ.'%');
            });
        }

        foreach ($includeSlugs as $slug) {
            $query->whereHas('genres', fn (Builder $gq) => $gq->where('slug', $slug));
        }

        if ($excludeSlugs !== []) {
            $query->whereDoesntHave('genres', fn (Builder $gq) => $gq->whereIn('slug', $excludeSlugs));
        }

        if ($statusTokens !== []) {
            $enums = array_values(array_intersect($statusTokens, ['ongoing', 'completed', 'hiatus', 'cancelled']));
            $wantsOneshot = in_array('oneshot', $statusTokens, true);
            if ($enums !== [] || $wantsOneshot) {
                $query->where(function (Builder $w) use ($enums, $wantsOneshot): void {
                    if ($enums !== [] && $wantsOneshot) {
                        $w->whereIn('status', $enums)
                            ->orWhereHas('tags', fn (Builder $tq) => $tq->where('slug', 'oneshot'));
                    } elseif ($enums !== []) {
                        $w->whereIn('status', $enums);
                    } elseif ($wantsOneshot) {
                        $w->whereHas('tags', fn (Builder $tq) => $tq->where('slug', 'oneshot'));
                    }
                });
            }
        }
    }

    private function applyAdvancedSearchSort(Builder $query, string $sort): void
    {
        match ($sort) {
            'old' => $query->oldest('updated_at'),
            'views' => $query->orderByDesc('total_views')->orderByDesc('updated_at'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count')->orderByDesc('updated_at'),
            'completed' => $query->where('status', 'completed')->latest('updated_at'),
            default => $query->latest('updated_at'),
        };
    }

    /**
     * @param  array<string, string>  $sortLabels
     * @return list<array{key: string, label: string, url: string}>
     */
    private function buildAdvancedSearchSortOptions(
        string $q,
        array $includeSlugs,
        array $excludeSlugs,
        array $statusTokens,
        array $sortLabels,
    ): array {
        $base = [
            'apply' => '1',
            'q' => $q,
            'includeGenres' => implode(',', $includeSlugs),
            'excludeGenres' => implode(',', $excludeSlugs),
            'status' => implode(',', $statusTokens),
        ];

        $options = [];
        foreach ($sortLabels as $key => $label) {
            $params = array_merge($base, ['page' => 1]);
            if ($key === 'new') {
                unset($params['sort']);
            } else {
                $params['sort'] = $key;
            }
            $options[] = [
                'key' => $key,
                'label' => $label,
                'url' => route('search.advanced').'?'.http_build_query($params),
            ];
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    private function parseCommaSeparatedQueryParam(mixed $raw): array
    {
        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $parts = array_map('trim', explode(',', $raw));

        return array_values(array_unique(array_filter($parts, fn (string $s): bool => $s !== '')));
    }

    /**
     * @return Collection<string, Collection<int, Genre>>
     */
    private function genresGroupedByLetterForAdvancedSearch(): Collection
    {
        return Genre::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->groupBy(function (Genre $genre): string {
                $first = mb_strtoupper(mb_substr(trim($genre->name), 0, 1, 'UTF-8'));
                if ($first === '' || preg_match('/^[0-9]/u', $first)) {
                    return '#';
                }

                return $first;
            })
            ->sortKeysUsing(function (string $a, string $b): int {
                if ($a === '#' && $b !== '#') {
                    return -1;
                }
                if ($b === '#' && $a !== '#') {
                    return 1;
                }

                return strcmp($a, $b);
            })
            ->map(function (mixed $group): Collection {
                if ($group instanceof Collection) {
                    return $group;
                }
                if (is_array($group)) {
                    return collect($group);
                }

                return collect();
            });
    }

    public function showRandom()
    {
        $manga = Manga::query()->published()->inRandomOrder()->first();
        if (! $manga) {
            abort(404);
        }
        return redirect()->to($manga->getUrl());
    }

    public function showProfile(string $user)
    {
        $user = User::where('id', $user)->first();
        if (! $user) {
            abort(404);
        }

        SEOMeta::setTitle($user->name . ' | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('profile', $user->id));

        if (Auth::check()) {
            if (Auth::user()->id === $user->id) {
                $user->load(['waifus', 'companionWaifu']);
                $ownedWaifuIds = $user->waifus->pluck('waifu_id')->unique()->values()->all();

                $allWaifus = Waifu::query()
                    ->where('is_active', true)
                    ->whereIn('rarity', [5, 4, 3])
                    ->orderByDesc('rarity')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug', 'rarity', 'image']);

                $waifuGrandTotal = $allWaifus->count();
                $waifuOwnedTotal = $user->waifus->count();
                $waifuCollectPercent = $waifuGrandTotal > 0
                    ? (int) min(100, (int) round(100 * $waifuOwnedTotal / $waifuGrandTotal))
                    : 0;

                $waifuTiers = [];
                foreach ([5, 4, 3] as $rarity) {
                    $items = $allWaifus->where('rarity', $rarity)->values();
                    $ownedInTier = $items->filter(fn (Waifu $w): bool => in_array((int) $w->id, $ownedWaifuIds, true))->count();
                    $waifuTiers[$rarity] = [
                        'total' => $items->count(),
                        'owned_in_tier' => $ownedInTier,
                        'waifus' => $items,
                    ];
                }

                $companionWaifu = $user->companionWaifu;

                $breakthroughPreview = app(LevelBreakthroughService::class)->preview($user);

                return view($this->prefixTheme.'::user.profile', compact(
                    'user',
                    'waifuTiers',
                    'ownedWaifuIds',
                    'waifuGrandTotal',
                    'waifuOwnedTotal',
                    'waifuCollectPercent',
                    'companionWaifu',
                    'breakthroughPreview',
                ));
            }
        }

        $user->loadCount([
            'mangas as mangas_published_count' => fn (Builder $q) => $q->whereNotNull('published_at'),
        ]);

        $mangas = Manga::query()
            ->published()
            ->with('genres')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $waifus = UserWaifu::query()
            ->where('user_id', $user->id)
            ->join('waifus as w', 'w.id', '=', 'user_waifus.waifu_id')
            ->orderByDesc('w.rarity')
            ->orderByDesc('user_waifus.quantity')
            ->orderByDesc('user_waifus.id')
            ->select('user_waifus.*')
            ->limit(5)
            ->with(['waifu' => fn (BelongsTo $relation) => $relation->select('id', 'name', 'slug', 'rarity', 'image')])
            ->get();

        return view($this->prefixTheme.'::profile', compact('user', 'mangas', 'waifus'));
    }

    public function showBlacklistTags()
    {
        /** @var User $user */
        $user = Auth::user();
        $genresByLetter = $this->genresGroupedByLetterForAdvancedSearch();
        $hiddenIds = array_map('intval', $user->hidden_genre_ids ?? []);
        sort($hiddenIds, SORT_NUMERIC);
        $hiddenGenres = collect();
        if ($hiddenIds !== []) {
            $hiddenGenres = Genre::query()
                ->whereIn('id', $hiddenIds)
                ->orderBy('name')
                ->get(['id', 'name', 'slug']);
        }

        SEOMeta::setTitle('Lọc thể loại không thích | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.blacklist-tags'));

        return view($this->prefixTheme.'::user.blacklist-tags', compact('user', 'genresByLetter', 'hiddenIds', 'hiddenGenres'));
    }

    /**
     * Lưu danh sách id thể loại user không muốn thấy (trên feed / danh sách — tích hợp filter sau).
     */
    public function updateBlacklistTags(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'genre_ids' => ['nullable', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
        ]);

        $ids = array_map('intval', $validated['genre_ids'] ?? []);
        $ids = array_values(array_unique($ids));
        sort($ids, SORT_NUMERIC);

        /** @var User $user */
        $user = Auth::user();
        $user->hidden_genre_ids = $ids;
        $user->save();

        return redirect()
            ->route('user.blacklist-tags')
            ->with('status', 'Đã lưu danh sách ẩn thể loại.');
    }

    public function showProfileEdit()
    {
        $user = Auth::user();

        SEOMeta::setTitle('Sửa hồ sơ | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.profile-edit'));

        return view($this->prefixTheme.'::user.profile-edit', compact('user'));
    }

    /**
     * Trang đột phá cấp độ (đủ EXP trong cấp hiện tại).
     */
    public function showLevelBreakthrough()
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $svc = app(LevelBreakthroughService::class);
        $preview = $svc->preview($user);
        $hasBreakthroughFlash = session()->has('breakthrough_effect')
            || session()->has('status')
            || session()->has('error');
        if ($preview === null && ! $hasBreakthroughFlash) {
            return redirect()
                ->route('profile', $user->id)
                ->with('status', 'Bạn chưa đủ kinh nghiệm để đột phá hoặc đã đạt cấp tối đa.');
        }

        SEOMeta::setTitle('Đột phá cấp độ | '.env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.level-breakthrough'));

        return view($this->prefixTheme.'::user.level-breakthrough', compact('user', 'preview'));
    }

    /**
     * Thực hiện đột phá (POST).
     */
    public function attemptLevelBreakthrough(Request $request, LevelBreakthroughService $svc): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user !== null, 403);

        try {
            $result = $svc->attempt($user, $request->boolean('use_protection'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('user.level-breakthrough')
                ->withErrors($e->errors());
        }

        if (($result['success'] ?? false) === true) {
            return redirect()
                ->route('user.level-breakthrough')
                ->with('status', $result['message'] ?? 'Đột phá thành công.')
                ->with('breakthrough_effect', 'success');
        }

        return redirect()
            ->route('user.level-breakthrough')
            ->with('error', $result['message'] ?? 'Đột phá thất bại.')
            ->with('breakthrough_effect', 'fail');
    }

    public function showManageManga()
    {
        $user = Auth::user();
        $user->loadSum('mangas', 'total_views');

        SEOMeta::setTitle('Quản lý truyện | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.manage-manga'));

        $mangas = Manga::query()
            ->where('user_id', $user->id)
            ->withCount('chapters')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view($this->prefixTheme.'::user.manage-manga', compact('user', 'mangas'));
    }

    public function showMangaPreview(string $manga)
    {
        $manga = Manga::query()
            ->where('slug', $manga)
            ->with(['authors', 'translators', 'genres', 'chapters'])
            ->firstOrFail();

        SEOMeta::setTitle($manga->title .' | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('mangas.preview', $manga->slug));

        return view($this->prefixTheme.'::user.manga-preview', compact('manga'));
    }

    public function showMangaEdit(string $manga)
    {
        $manga = Manga::where('slug', $manga)->with(['authors', 'translators', 'genres'])->first();
        if (! $manga) {
            abort(404);
        }
        if ((int) $manga->user_id !== (int) Auth::id()) {
            abort(403);
        }

        SEOMeta::setTitle('Sửa truyện | '.env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('mangas.edit', $manga->slug));

        $genreGroups = $this->genresGroupedByLetterForForm();
        $mangaFormAction = route('user.update-manga', ['mangaSlug' => $manga->slug]);
        $pageTitle = 'Chỉnh sửa truyện';

        return view($this->prefixTheme.'::user.manga-form-page', compact('manga', 'genreGroups', 'mangaFormAction', 'pageTitle'));
    }

    public function showCreateManga()
    {
        SEOMeta::setTitle('Đăng truyện | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.create-manga'));

        $manga = null;
        $genreGroups = $this->genresGroupedByLetterForForm();
        $mangaFormAction = route('user.store-manga');
        $pageTitle = 'Đăng truyện';
        $pageSubtitle = null;

        return view($this->prefixTheme.'::user.manga-form-page', compact('manga', 'genreGroups', 'mangaFormAction', 'pageTitle', 'pageSubtitle'));
    }

    /**
     * Nhóm thể loại theo chữ cái đầu (dùng form đăng/sửa truyện).
     */
    protected function genresGroupedByLetterForForm(): Collection
    {
        return Genre::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->groupBy(function (Genre $genre): string {
                $first = mb_strtoupper(mb_substr(trim($genre->name), 0, 1, 'UTF-8'));
                if ($first === '' || preg_match('/^[0-9]/u', $first)) {
                    return '#';
                }

                return $first;
            })
            ->sortKeysUsing(function (string $a, string $b): int {
                if ($a === '#' && $b !== '#') {
                    return -1;
                }
                if ($b === '#' && $a !== '#') {
                    return 1;
                }

                return strcmp($a, $b);
            })
            ->map(function (mixed $group): Collection {
                if ($group instanceof Collection) {
                    return $group;
                }
                if (is_array($group)) {
                    return collect($group);
                }

                return collect();
            });
    }

    public function showCreateChapter(string $manga)
    {
        $manga = Manga::where('slug', $manga)->first();
        if (! $manga) {
            abort(404);
        }
        $user = Auth::user();
        if ($user === null || (int) $manga->user_id !== (int) $user->id) {
            abort(403);
        }

        SEOMeta::setTitle('Thêm chương | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.create-chapter', $manga->slug));

        return view($this->prefixTheme.'::user.create-chapter', compact('manga'));
    }

    /**
     * Sửa chương — cùng view/form với thêm chương.
     */
    public function showEditChapter(string $manga, Chapter $chapter)
    {
        $manga = Manga::where('slug', $manga)->first();
        if (! $manga) {
            abort(404);
        }
        if ((int) $chapter->manga_id !== (int) $manga->id) {
            abort(404);
        }
        $user = Auth::user();
        if ($user === null || (int) $manga->user_id !== (int) $user->id) {
            abort(403);
        }

        SEOMeta::setTitle('Sửa chương | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('user.edit-chapter', [$manga->slug, $chapter->id]));

        return view($this->prefixTheme.'::user.create-chapter', compact('manga', 'chapter'));
    }

    public function showShop()
    {
        SEOMeta::setTitle('Cửa hàng | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('shop'));

        $shopItems = ShopItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        /** @var User|null $auth */
        $auth = Auth::user();
        $userPoints = $auth !== null ? (int) $auth->points : 0;

        $quantitiesByItemId = [];
        if ($auth !== null && $shopItems->isNotEmpty()) {
            $quantitiesByItemId = $auth->userItems()
                ->whereIn('shop_item_id', $shopItems->modelKeys())
                ->pluck('quantity', 'shop_item_id')
                ->all();
        }

        return view($this->prefixTheme.'::shop', compact('shopItems', 'userPoints', 'quantitiesByItemId'));
    }

    public function showWaifuSummon()
    {
        SEOMeta::setTitle('Triệu hồi Waifu | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('waifu.summon'));

        $rewardSummonsTotal = $this->countWaifuSummonRollItems();
        $userWaifuPoints = (int) (Auth::user()?->points ?? 0);
        $waifuRewardPityCap = WaifuSummonConfig::PITY_CAP;
        $waifuSummonClaimedMilestones = [];
        if (Auth::check() && Schema::hasTable('waifu_summon_milestone_claims')) {
            $waifuSummonClaimedMilestones = WaifuSummonMilestoneClaim::query()
                ->where('user_id', Auth::id())
                ->orderBy('milestone')
                ->pluck('milestone')
                ->all();
        }
        $summonVideoUrl = (string) active_theme_config(
            'waifu_summon_video_url',
            asset('vendor/theme-vinahentai/videos/summon.mp4'),
        );
        $summonBackgroundWebp = (string) active_theme_config(
            'waifu_summon_background_webp_url',
            asset('vendor/theme-vinahentai/images/background.webp'),
        );

        return view($this->prefixTheme.'::waifu-summon', compact(
            'rewardSummonsTotal',
            'userWaifuPoints',
            'waifuRewardPityCap',
            'waifuSummonClaimedMilestones',
            'summonVideoUrl',
            'summonBackgroundWebp',
        ));
    }

    /**
     * Tổng lượt triệu hồi (mỗi dòng log = 1 lượt; x10 = 10).
     */
    private function countWaifuSummonRollItems(): int
    {
        if (! Auth::check() || ! Schema::hasTable('waifu_summon_logs')) {
            return 0;
        }

        return (int) WaifuSummonLog::query()->where('user_id', Auth::id())->count();
    }

    public function showDanhSach(Request $request)
    {
        SEOMeta::setTitle('Danh sách truyện | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('danh-sach'));

        $genres = Genre::query()
            ->orderBy('name')
            ->get();

        $sort = (string) $request->query('sort', 'new');
        $allowedSorts = ['new', 'old', 'views', 'rating', 'completed'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'new';
        }

        $status = (string) $request->query('status', '');
        $allowedStatuses = ['ongoing', 'completed'];
        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $query = Manga::query()
            ->published()
            ->with('genres');

        match ($sort) {
            'old' => $query->oldest('updated_at'),
            'views' => $query->orderByDesc('total_views')->orderByDesc('updated_at'),
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('ratings_count')->orderByDesc('updated_at'),
            'completed' => $query->where('status', 'completed')->latest('updated_at'),
            default => $query->latest('updated_at'),
        };

        if ($status !== '') {
            $query->whereIn('status', explode(',', $status));
        }

        $mangas = $query->paginate(40)->withQueryString();

        $sortLabels = [
            'new' => 'Mới cập nhật',
            'old' => 'Cũ nhất',
            'views' => 'Đọc nhiều',
            'rating' => 'Đánh giá cao',
            'completed' => 'Đã hoàn thành',
        ];

        $preserveQuery = $request->except(['sort', 'page']);
        $sortOptions = [];
        foreach ($sortLabels as $key => $label) {
            $q = array_merge($preserveQuery, ['page' => 1]);
            if ($key === 'new') {
                unset($q['sort']);
            } else {
                $q['sort'] = $key;
            }
            $sortOptions[] = [
                'key' => $key,
                'label' => $label,
                'url' => route('danh-sach').'?'.http_build_query($q),
            ];
        }

        return view($this->prefixTheme.'::danh-sach', [
            'mangas' => $mangas,
            'genres' => $genres,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'currentSortLabel' => $sortLabels[$sort] ?? $sortLabels['new'],
        ]);
    }

    public function showGioiThieu()
    {
        SEOMeta::setTitle('Giới thiệu | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('gioi-thieu'));

        return view($this->prefixTheme.'::gioi-thieu');
    }

    public function showGenres()
    {
        SEOMeta::setTitle('Tất cả thể loại truyện | ' . env('APP_NAME'), false)
            ->setDescription(active_theme_config('seo_home_description', ''))
            ->setCanonical(route('genres'));

        $genres = Genre::query()
            ->orderBy('name')
            ->get();

        return view($this->prefixTheme.'::genres', compact('genres'));
    }
}
