<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserChapterRequest;
use App\Http\Requests\UpdateUserChapterRequest;
use App\Models\Chapter;
use App\Models\Manga;
use App\Services\ChapterPageWatermarkService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class UserChapterController extends Controller
{
    private const string TZ_HEN_GIO = 'Asia/Ho_Chi_Minh';

    public function __construct(
        private readonly ChapterPageWatermarkService $watermarkTrangChuong,
    ) {}

    /**
     * Kiểm tra chương mới có trùng tiêu đề / số chương / slug cơ sở không (chỉ chủ truyện).
     */
    public function checkDuplicate(Request $request, string $mangaSlug): JsonResponse
    {
        $manga = Manga::query()->where('slug', $mangaSlug)->firstOrFail();
        $user = $request->user();
        if ($user === null || (int) $manga->user_id !== (int) $user->id) {
            abort(403);
        }

        $title = trim((string) $request->query('title', ''));
        $chapterNumberRaw = $request->query('chapter_number');
        $chapterNumber = is_numeric($chapterNumberRaw) ? (float) $chapterNumberRaw : null;

        $maxNum = Chapter::query()->where('manga_id', $manga->id)->max('chapter_number');
        $nextChapterNumber = $maxNum !== null ? ((float) $maxNum) + 1.0 : 1.0;

        $titleTaken = false;
        if ($title !== '') {
            $titleTaken = Chapter::query()
                ->where('manga_id', $manga->id)
                ->where('title', $title)
                ->exists();
        }

        $chapterNumberTaken = false;
        if ($chapterNumber !== null) {
            $chapterNumberTaken = Chapter::query()
                ->where('manga_id', $manga->id)
                ->where('chapter_number', $chapterNumber)
                ->exists();
        }

        $baseSlug = $title !== '' ? Str::slug($title) : '';
        if ($baseSlug === '' && $title !== '') {
            $baseSlug = 'item-'.substr(md5($title), 0, 10);
        }
        $baseSlugTaken = false;
        if ($baseSlug !== '') {
            $baseSlugTaken = Chapter::query()
                ->where('manga_id', $manga->id)
                ->where('slug', $baseSlug)
                ->exists();
        }

        $hasConflict = $titleTaken || $chapterNumberTaken;

        return response()->json([
            'ok' => ! $hasConflict,
            'next_chapter_number' => $nextChapterNumber,
            'conflicts' => [
                'title' => $titleTaken,
                'chapter_number' => $chapterNumberTaken,
                'base_slug' => $baseSlugTaken,
            ],
            'messages' => [
                'title' => $titleTaken ? 'Đã có chương cùng tiêu đề này.' : null,
                'chapter_number' => $chapterNumberTaken ? 'Số chương này đã tồn tại.' : null,
                'base_slug' => $baseSlugTaken ? 'Slug từ tiêu đề đã tồn tại; khi lưu hệ thống có thể thêm hậu tố.' : null,
            ],
        ]);
    }

    /**
     * Lưu chương mới (upload ảnh lên disk public), chỉ chủ truyện.
     */
    public function store(StoreUserChapterRequest $request, string $mangaSlug): JsonResponse
    {
        $manga = Manga::query()->where('slug', $mangaSlug)->firstOrFail();
        $validated = $request->validated();
        $title = trim($validated['title']);

        if (Chapter::query()->where('manga_id', $manga->id)->where('title', $title)->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'Đã có chương cùng tiêu đề này.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $publishedAt = $this->resolvePublishedAt($validated);

        if ($publishedAt === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Thời gian hẹn đăng phải ở tương lai.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $maxNum = Chapter::query()->where('manga_id', $manga->id)->max('chapter_number');
        $nextChapterNumber = $maxNum !== null ? ((float) $maxNum) + 1.0 : 1.0;

        $slugBase = Str::slug($title);
        if ($slugBase === '' && $title !== '') {
            $slugBase = 'item-'.substr(md5($title), 0, 10);
        }
        if ($slugBase === '') {
            $slugBase = 'chap-'.rtrim(rtrim(number_format($nextChapterNumber, 2, '.', ''), '0'), '.');
        }

        $slug = $slugBase;
        $i = 1;
        while (Chapter::query()->where('manga_id', $manga->id)->where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$i;
            $i++;
        }

        $directory = 'chapters/'.$manga->id.'/'.Str::uuid()->toString();
        $urls = [];
        $kieuWm = $validated['watermark_style'] ?? 'stroke';
        if (! in_array($kieuWm, ['glow', 'stroke'], true)) {
            $kieuWm = 'stroke';
        }

        try {
            foreach ($request->file('pages', []) as $file) {
                $path = $this->watermarkTrangChuong->xuLyVaLuuLenPublic($file, $directory, $kieuWm);
                $urls[] = url(Storage::disk('public')->url($path));
            }
        } catch (Throwable) {
            $this->xoaThuMucAnhChuongTrenPublic($directory);

            return response()->json([
                'ok' => false,
                'message' => 'Không xử lý được ảnh (watermark). Kiểm tra định dạng file hoặc thử lại.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $imageServers = [
            'Mặc định' => $urls,
        ];

        try {
            DB::transaction(function () use ($manga, $title, $slug, $nextChapterNumber, $imageServers, $publishedAt, $urls): void {
                Chapter::query()->create([
                    'manga_id' => $manga->id,
                    'title' => $title,
                    'slug' => $slug,
                    'chapter_number' => $nextChapterNumber,
                    'image_servers' => $imageServers,
                    'pages_count' => count($urls),
                    'published_at' => $publishedAt,
                ]);
            });
        } catch (Throwable) {
            $this->xoaThuMucAnhChuongTrenPublic($directory);

            return response()->json([
                'ok' => false,
                'message' => 'Không lưu được chương. Ảnh tạm đã được dọn.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'ok' => true,
            'redirect' => route('mangas.preview', $manga->slug),
            'message' => $validated['publish_mode'] === 'scheduled'
                ? 'Đã hẹn giờ đăng chương.'
                : 'Đã đăng chương.',
        ]);
    }

    /**
     * Cập nhật chương (tiêu đề / lịch; tùy chọn thay toàn bộ ảnh khi gửi pages[]).
     */
    public function update(UpdateUserChapterRequest $request, string $mangaSlug, Chapter $chapter): JsonResponse
    {
        $manga = Manga::query()->where('slug', $mangaSlug)->firstOrFail();
        if ((int) $chapter->manga_id !== (int) $manga->id) {
            abort(404);
        }

        $validated = $request->validated();
        $title = trim($validated['title']);
        $publishedAt = $this->resolvePublishedAt($validated);

        if ($publishedAt === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Thời gian hẹn đăng phải ở tương lai.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $slug = $chapter->slug;
        if ($title !== $chapter->title) {
            $slugBase = Str::slug($title);
            if ($slugBase === '' && $title !== '') {
                $slugBase = 'item-'.substr(md5($title), 0, 10);
            }
            if ($slugBase === '') {
                $slugBase = 'chap-'.rtrim(rtrim(number_format((float) $chapter->chapter_number, 2, '.', ''), '0'), '.');
            }
            $slug = $slugBase;
            $i = 1;
            while (Chapter::query()
                ->where('manga_id', $manga->id)
                ->where('slug', $slug)
                ->where('id', '!=', $chapter->id)
                ->exists()) {
                $slug = $slugBase.'-'.$i;
                $i++;
            }
        }

        $imageServers = $chapter->image_servers;
        $pagesCount = (int) $chapter->pages_count;

        if ($request->hasFile('pages')) {
            $anhCuPayload = $chapter->image_servers;
            $directory = 'chapters/'.$manga->id.'/'.Str::uuid()->toString();
            $urls = [];
            $kieuWm = $validated['watermark_style'] ?? 'stroke';
            if (! in_array($kieuWm, ['glow', 'stroke'], true)) {
                $kieuWm = 'stroke';
            }
            try {
                foreach ($request->file('pages', []) as $file) {
                    $path = $this->watermarkTrangChuong->xuLyVaLuuLenPublic($file, $directory, $kieuWm);
                    $urls[] = url(Storage::disk('public')->url($path));
                }
            } catch (Throwable) {
                $this->xoaThuMucAnhChuongTrenPublic($directory);

                return response()->json([
                    'ok' => false,
                    'message' => 'Không xử lý được ảnh (watermark). Kiểm tra định dạng file hoặc thử lại.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $imageServers = [
                'Mặc định' => $urls,
            ];
            $pagesCount = count($urls);

            try {
                DB::transaction(function () use ($chapter, $title, $slug, $publishedAt, $imageServers, $pagesCount): void {
                    $chapter->update([
                        'title' => $title,
                        'slug' => $slug,
                        'published_at' => $publishedAt,
                        'image_servers' => $imageServers,
                        'pages_count' => $pagesCount,
                    ]);
                });
            } catch (Throwable) {
                $this->xoaThuMucAnhChuongTrenPublic($directory);

                return response()->json([
                    'ok' => false,
                    'message' => 'Không cập nhật được chương. Ảnh mới tạm đã được dọn.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->xoaAnhTheoImageServersPayload(is_array($anhCuPayload) ? $anhCuPayload : []);
        } else {
            DB::transaction(function () use ($chapter, $title, $slug, $publishedAt, $imageServers, $pagesCount): void {
                $chapter->update([
                    'title' => $title,
                    'slug' => $slug,
                    'published_at' => $publishedAt,
                    'image_servers' => $imageServers,
                    'pages_count' => $pagesCount,
                ]);
            });
        }

        return response()->json([
            'ok' => true,
            'redirect' => route('mangas.preview', $manga->slug),
            'message' => $validated['publish_mode'] === 'scheduled'
                ? 'Đã cập nhật chương (hẹn giờ đăng).'
                : 'Đã cập nhật chương.',
        ]);
    }

    /**
     * Xóa chương và thư mục ảnh trên disk public (nếu có).
     */
    public function destroy(Request $request, string $mangaSlug, Chapter $chapter): JsonResponse
    {
        $manga = Manga::query()->where('slug', $mangaSlug)->firstOrFail();
        if ((int) $chapter->manga_id !== (int) $manga->id) {
            abort(404);
        }
        $user = $request->user();
        if ($user === null || (int) $manga->user_id !== (int) $user->id) {
            abort(403);
        }

        $this->xoaAnhTheoImageServersPayload(is_array($chapter->image_servers) ? $chapter->image_servers : []);
        $chapter->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Đã xóa chương.',
        ]);
    }

    /**
     * Xóa cả thư mục upload chương (chapters/{manga_id}/{uuid}/) khi path nằm trong public disk.
     */
    private function xoaThuMucAnhChuongTrenPublic(string $pathThuMucTuongDoi): void
    {
        $trim = trim($pathThuMucTuongDoi, '/');
        if ($trim === '' || $trim === '.' || str_contains($trim, '..')) {
            return;
        }
        if (! Str::startsWith($trim, 'chapters/')) {
            return;
        }
        if (Storage::disk('public')->exists($trim)) {
            Storage::disk('public')->deleteDirectory($trim);
        }
    }

    /**
     * Xóa toàn bộ thư mục ảnh được tham chiếu trong image_servers (chỉ path chapters/... trên disk public).
     */
    private function xoaAnhTheoImageServersPayload(array $servers): void
    {
        $dirs = $this->thuMucLuutruTuImageServers($servers);
        foreach ($dirs as $dir) {
            if ($dir !== '.' && $dir !== '' && $dir !== DIRECTORY_SEPARATOR) {
                Storage::disk('public')->deleteDirectory($dir);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function thuMucLuutruTuImageServers(array $servers): array
    {
        $dirs = [];
        foreach ($servers as $bucket) {
            if (! is_array($bucket)) {
                continue;
            }
            foreach (Arr::flatten($bucket) as $url) {
                if (! is_string($url) || trim($url) === '') {
                    continue;
                }
                $relative = $this->publicUrlToRelativeStoragePath($url);
                if ($relative === null || ! Str::startsWith($relative, 'chapters/')) {
                    continue;
                }
                $dirs[] = dirname($relative);
            }
        }

        return array_values(array_unique($dirs));
    }

    private function publicUrlToRelativeStoragePath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            if (str_starts_with($url, '/storage/')) {
                $path = $url;
            } else {
                return null;
            }
        }

        $trimmed = ltrim($path, '/');
        if (str_starts_with($trimmed, 'storage/')) {
            return substr($trimmed, strlen('storage/'));
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolvePublishedAt(array $validated): ?Carbon
    {
        if ($validated['publish_mode'] === 'immediate') {
            return now();
        }

        $dateStr = (string) $validated['schedule_date'];
        $h = (int) $validated['schedule_hour'];
        $m = (int) $validated['schedule_minute'];

        $dt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            sprintf('%s %02d:%02d:00', $dateStr, $h, $m),
            self::TZ_HEN_GIO
        );

        if ($dt === false) {
            return null;
        }

        if ($dt->lessThanOrEqualTo(now())) {
            return null;
        }

        return $dt;
    }
}
