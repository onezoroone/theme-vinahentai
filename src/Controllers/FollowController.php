<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Manga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class FollowController extends Controller
{
    public function status(Request $request, Manga $manga): JsonResponse
    {
        abort_unless($manga->published_at !== null, 404);

        $user = $request->user();
        $followed = in_array($manga->id, $user->followedMangaIds(), true);

        return response()->json([
            'followed' => $followed,
            'total_follows' => (int) ($manga->total_follows ?? 0),
        ]);
    }

    public function toggle(Request $request, Manga $manga): JsonResponse
    {
        abort_unless($manga->published_at !== null, 404);

        $user = $request->user();

        $payload = DB::transaction(function () use ($user, $manga): array {
            $manga = Manga::query()->lockForUpdate()->findOrFail($manga->id);

            $ids = $user->followedMangaIds();
            $isFollowing = in_array($manga->id, $ids, true);

            if ($isFollowing) {
                $ids = array_values(array_filter($ids, fn (int $id): bool => $id !== $manga->id));
                $manga->update([
                    'total_follows' => max(0, (int) $manga->total_follows - 1),
                ]);
            } else {
                $ids[] = $manga->id;
                $ids = array_values(array_unique($ids));
                $manga->update([
                    'total_follows' => (int) $manga->total_follows + 1,
                ]);
            }

            $user->forceFill([
                'followed_manga_ids' => json_encode($ids, JSON_UNESCAPED_UNICODE),
            ])->save();

            return [
                'followed' => ! $isFollowing,
                'total_follows' => (int) $manga->total_follows,
            ];
        });

        return response()->json($payload);
    }
}
