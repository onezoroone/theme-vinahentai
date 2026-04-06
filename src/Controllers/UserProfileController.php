<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use App\Models\UserWaifu;
use App\Models\Waifu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

final class UserProfileController extends Controller
{
    /**
     * API cập nhật tên, giới thiệu, ảnh đại diện (multipart).
     */
    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->bio = $validated['bio'] ?? null;

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = Storage::disk('public')->url($path);
        }

        $user->save();

        return response()->json([
            'message' => 'Đã cập nhật hồ sơ.',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'bio' => $user->bio,
            ],
        ]);
    }

    /**
     * API đổi mật khẩu (cần đúng mật khẩu hiện tại).
     */
    public function updatePassword(UpdateUserPasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->password = $request->validated('password');
        $user->save();

        return response()->json([
            'message' => 'Đã đổi mật khẩu.',
        ]);
    }

    /**
     * Chọn / bỏ waifu đồng hành (chỉ waifu đã sở hữu).
     */
    public function updateCompanionWaifu(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'waifu_id' => ['nullable', 'integer', 'exists:waifus,id'],
        ]);

        /** @var User $user */
        $user = Auth::user();
        $waifuId = $validated['waifu_id'] ?? null;

        if ($waifuId === null) {
            $user->companion_waifu_id = null;
            $user->save();

            return response()->json([
                'ok' => true,
                'companion' => null,
            ]);
        }

        $owns = UserWaifu::query()
            ->where('user_id', $user->id)
            ->where('waifu_id', $waifuId)
            ->exists();

        if (! $owns) {
            return response()->json([
                'message' => 'Bạn chưa sở hữu waifu này.',
            ], 422);
        }

        $user->companion_waifu_id = $waifuId;
        $user->save();

        $waifu = Waifu::query()->find($waifuId);

        return response()->json([
            'ok' => true,
            'companion' => $waifu === null ? null : [
                'id' => $waifu->id,
                'name' => (string) $waifu->name,
                'image' => $waifu->image !== null && $waifu->image !== '' ? asset($waifu->image) : null,
            ],
        ]);
    }
}
