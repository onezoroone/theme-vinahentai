<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Models\Chapter;
use App\Models\ChapterReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ChapterReportController
{
    public function report(Request $request, Chapter $chapter): JsonResponse
    {
        abort_unless($chapter->isVisibleAlongManga($request->user()), 404);

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $userId = (int) $request->user()->id;
        $chapterId = (int) $chapter->id;

        if (ChapterReport::query()
            ->where('chapter_id', $chapterId)
            ->where('user_id', $userId)
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'content' => ['Bạn đã báo cáo chương này rồi.'],
            ]);
        }

        DB::transaction(function () use ($chapterId, $userId, $validated): void {
            ChapterReport::query()->create([
                'chapter_id' => $chapterId,
                'user_id' => $userId,
                'content' => trim((string) $validated['content']),
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Đã gửi báo cáo.',
        ]);
    }
}

