<?php

namespace Nqt\ThemeVinahentai\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShopPurchaseRequest;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use App\Models\UserItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Mua vật phẩm cửa hàng bằng Dâm Ngọc (points).
 */
final class ShopController extends Controller
{
    public function purchase(ShopPurchaseRequest $request): RedirectResponse
    {
        $itemId = (int) $request->validated('shop_item_id');
        /** @var ShopItem $item */
        $item = ShopItem::query()->whereKey($itemId)->where('is_active', true)->firstOrFail();

        $userId = Auth::id();
        if ($userId === null) {
            abort(403);
        }

        $pricePoints = (int) $item->price_points;
        $priceGold = (int) $item->price_gold;

        try {
            DB::beginTransaction();

            /** @var User $user */
            $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();

            if ((int) $user->points < $pricePoints) {
                DB::rollBack();

                return redirect()->route('shop')->with('shop_error', 'Không đủ Dâm Ngọc.');
            }

            $user->decrement('points', $pricePoints);

            $row = UserItem::query()->firstOrNew([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
            ]);
            $row->quantity = (int) $row->quantity + 1;
            $row->last_obtained_at = now();
            $row->save();

            ShopPurchase::query()->create([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
                'quantity' => 1,
                'unit_price_points' => $pricePoints,
                'unit_price_gold' => $priceGold,
                'total_price_points' => $pricePoints,
                'total_price_gold' => $priceGold,
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);

            return redirect()->route('shop')->with('shop_error', 'Không thể hoàn tất giao dịch. Thử lại sau.');
        }

        return redirect()->route('shop')->with('shop_success', 'Đã mua thành công.');
    }
}
