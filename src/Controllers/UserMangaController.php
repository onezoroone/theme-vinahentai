<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserMangaSaveRequest;
use App\Models\Author;
use App\Models\Genre;
use App\Models\Manga;
use App\Models\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class UserMangaController extends Controller
{
    /**
     * Tạo truyện mới (chờ duyệt, chưa public).
     */
    public function store(UserMangaSaveRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $genreIds = $this->resolveGenreIdsForSync($request, $validated);

        $manga = DB::transaction(function () use ($request, $validated, $genreIds): Manga {
            $manga = Manga::query()->create([
                'title' => $validated['title'],
                'alternative_title' => $validated['alternateTitle'] ?? null,
                'description' => $validated['description'] ?? null,
                'cover_image' => $this->normalizeCoverUrl($validated['posterUrl'] ?? null),
                'status' => $validated['status'],
                'age_rating' => 'adult',
                'user_id' => $request->user()->id,
                'published_at' => null,
            ]);
            $manga->genres()->sync($genreIds);
            $manga->authors()->sync($this->resolveTaxonomyIdsForSync($request, 'author'));
            $manga->translators()->sync($this->resolveTaxonomyIdsForSync($request, 'translator'));

            return $manga;
        });

        return redirect()
            ->route('mangas.preview', $manga->slug)
            ->with('status', 'Đã lưu truyện. Bạn có thể tiếp tục chỉnh sửa hoặc thêm chương.');
    }

    /**
     * Cập nhật truyện của user (route param: mangaSlug).
     */
    public function update(UserMangaSaveRequest $request, string $mangaSlug): RedirectResponse
    {
        $manga = Manga::query()->where('slug', $mangaSlug)->firstOrFail();
        $validated = $request->validated();
        $genreIds = $this->resolveGenreIdsForSync($request, $validated);

        DB::transaction(function () use ($request, $manga, $validated, $genreIds): void {
            $manga->update([
                'title' => $validated['title'],
                'alternative_title' => $validated['alternateTitle'] ?? null,
                'description' => $validated['description'] ?? null,
                'cover_image' => $this->normalizeCoverUrl($validated['posterUrl'] ?? null) ?? $manga->cover_image,
                'status' => $validated['status'],
            ]);
            $manga->genres()->sync($genreIds);
            $manga->authors()->sync($this->resolveTaxonomyIdsForSync($request, 'author'));
            $manga->translators()->sync($this->resolveTaxonomyIdsForSync($request, 'translator'));
        });

        return redirect()
            ->route('mangas.edit', $manga->slug)
            ->with('status', 'Đã cập nhật truyện.');
    }

    /**
     * Upload ảnh bìa (tối đa 1MB), lưu disk public — trả URL đưa vào posterUrl khi lưu form.
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => ['required', 'file', 'image', 'max:1024', 'mimes:jpeg,jpg,png,webp,gif'],
        ]);

        $user = $request->user();
        $path = $request->file('cover')->store('manga-covers/'.$user->id, 'public');
        $relative = Storage::disk('public')->url($path);

        return response()->json([
            'url' => url($relative),
        ]);
    }

    /**
     * Xóa truyện do chính user đăng (chỉ khi user_id khớp).
     */
    public function destroy(Request $request, Manga $manga): JsonResponse
    {
        $user = $request->user();
        if ($manga->user_id === null || (int) $manga->user_id !== (int) $user->id) {
            abort(403);
        }

        $manga->delete();

        return response()->json([
            'message' => 'Đã xóa truyện.',
        ]);
    }

    /**
     * Gợi ý tác giả khi đăng truyện (tối đa 10, q ≥ 1 ký tự).
     */
    public function searchAuthors(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json(['data' => []]);
        }

        $like = '%'.$this->escapeLikePattern($q).'%';
        $rows = Author::query()
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit(10)
            ->withCount('mangas')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    /**
     * Gợi ý dịch giả khi đăng truyện (tối đa 10, q ≥ 1 ký tự).
     */
    public function searchTranslators(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json(['data' => []]);
        }

        $like = '%'.$this->escapeLikePattern($q).'%';
        $rows = Translator::query()
            ->where('name', 'like', $like)
            ->orderBy('name')
            ->limit(10)
            ->withCount('mangas')
            ->get(['id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    /**
     * Đọc mảng từ request (đã merge array hoặc chuỗi JSON từ input hidden).
     *
     * @return list<string>
     */
    private function requestStringList(Request $request, string $key): array
    {
        $raw = $request->input($key);
        if (is_array($raw)) {
            return array_values(array_map(
                static fn (mixed $v): string => is_string($v) ? trim($v) : trim((string) $v),
                $raw
            ));
        }
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map(
            static fn (mixed $v): string => is_string($v) ? trim($v) : trim((string) $v),
            $decoded
        ));
    }

    /**
     * @return list<int>
     */
    private function requestIntList(Request $request, string $key): array
    {
        $raw = $request->input($key);
        if (is_array($raw)) {
            return array_values(array_map(static fn (mixed $v): int => (int) $v, $raw));
        }
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map(static fn (mixed $v): int => (int) $v, $decoded));
    }

    /**
     * Ghép tên + id từ form: id hợp lệ thì dùng, không thì firstOrCreate theo name_md5 (giống admin).
     * Luôn đọc từ $request->input (không chỉ validated) để không mất dữ liệu khi validated() thiếu key.
     *
     * @return list<int>
     */
    private function resolveTaxonomyIdsForSync(Request $request, string $kind): array
    {
        $isTranslator = $kind === 'translator';
        $model = $isTranslator ? Translator::class : Author::class;
        $names = $this->requestStringList($request, $isTranslator ? 'translatorNames' : 'authorNames');
        $ids = $this->requestIntList($request, $isTranslator ? 'translatorIds' : 'authorIds');

        $n = max(count($names), count($ids));
        $result = [];
        $seen = [];
        for ($i = 0; $i < $n; $i++) {
            $name = isset($names[$i]) ? trim((string) $names[$i]) : '';
            if ($name === '') {
                continue;
            }
            $rawId = $ids[$i] ?? 0;
            $id = is_numeric($rawId) ? (int) $rawId : 0;
            if ($id > 0 && $model::query()->whereKey($id)->exists()) {
                $resolvedId = $id;
            } else {
                $resolvedId = (int) $model::query()->firstOrCreate(
                    ['name_md5' => md5($name)],
                    ['name' => $name]
                )->getKey();
            }
            if (! in_array($resolvedId, $seen, true)) {
                $seen[] = $resolvedId;
                $result[] = $resolvedId;
            }
        }

        return $result;
    }

    private function resolveGenreIdsForSync(Request $request, array $validated): array
    {
        $genreIds = array_values(array_unique(array_filter(array_map(
            'intval',
            $validated['genre_ids'] ?? []
        ), fn (int $id): bool => $id > 0)));

        $oneshotGenreId = Genre::query()->where('slug', 'oneshot')->value('id');
        if ($oneshotGenreId !== null) {
            $oid = (int) $oneshotGenreId;
            $genreIds = array_values(array_diff($genreIds, [$oid]));
            if ($request->boolean('oneshot')) {
                $genreIds[] = $oid;
            }
        }

        return array_values(array_unique(array_filter($genreIds, fn (int $id): bool => $id > 0)));
    }

    private function normalizeCoverUrl(?string $url): ?string
    {
        $url = $url !== null ? trim($url) : '';

        return $url === '' ? null : $url;
    }

    private function escapeLikePattern(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
