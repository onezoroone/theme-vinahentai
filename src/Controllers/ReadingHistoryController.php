<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Models\Chapter;
use App\Models\Manga;
use App\Models\ReadingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lưu / cập nhật / đọc lịch sử đọc cho user đã đăng nhập (một dòng / user + chapter).
 */
final class ReadingHistoryController
{
    /**
     * Trả về trang đang đọc (1-based) để client cuộn / slide tới khi vào chương.
     */
    public function show(Request $request, Chapter $chapter): JsonResponse
    {
        abort_unless($chapter->isVisibleAlongManga($request->user()), 404);

        $row = ReadingHistory::query()
            ->where('user_id', $request->user()->id)
            ->where('chapter_id', $chapter->id)
            ->first();

        return response()->json([
            'ok' => true,
            'last_read_page' => $row !== null ? (int) $row->last_read_page : null,
        ]);
    }

    /**
     * Lần đọc gần nhất của user với truyện này (theo read_at) — dùng nút "Đọc tiếp" trên trang manga.
     */
    public function latestForManga(Request $request, Manga $manga): JsonResponse
    {
        abort_unless($manga->isVisibleTo($request->user()), 404);

        $row = ReadingHistory::query()
            ->where('user_id', $request->user()->id)
            ->where('manga_id', $manga->id)
            ->orderByDesc('read_at')
            ->first();

        if ($row === null) {
            return response()->json([
                'ok' => true,
                'entry' => null,
            ]);
        }

        $chapter = Chapter::query()
            ->whereKey($row->chapter_id)
            ->where('manga_id', $manga->id)
            ->first();

        if ($chapter === null) {
            return response()->json([
                'ok' => true,
                'entry' => null,
            ]);
        }

        $chapter->setRelation('manga', $manga);

        $chapterNumberLabel = $this->formatChapterNumberLabel($chapter->chapter_number);

        return response()->json([
            'ok' => true,
            'entry' => [
                'url' => $chapter->getUrl(),
                'chapter_number' => $chapterNumberLabel,
                'chapter_title' => (string) ($chapter->title ?? ''),
                'last_read_page' => (int) $row->last_read_page,
            ],
        ]);
    }

    public function store(Request $request, Chapter $chapter): JsonResponse
    {
        abort_unless($chapter->isVisibleAlongManga($request->user()), 404);

        $validated = $request->validate([
            'last_read_page' => ['sometimes', 'integer', 'min:1', 'max:65535'],
        ]);

        $user = $request->user();
        $page = (int) ($validated['last_read_page'] ?? 1);
        if ($page < 1) {
            $page = 1;
        }

        ReadingHistory::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'chapter_id' => $chapter->id,
            ],
            [
                'manga_id' => $chapter->manga_id,
                'last_read_page' => $page,
                'read_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
        ]);
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
