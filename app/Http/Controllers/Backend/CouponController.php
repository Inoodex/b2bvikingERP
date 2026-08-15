<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CouponDataTable;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(CouponDataTable $dataTable)
    {
        return $dataTable->render('backend.coupons.index');
    }

    public function create(): View
    {
        $discounts = Discount::latest()->get();
        return view('backend.coupons.create', compact('discounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_id' => 'required|exists:discounts,id',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'status' => 'required|boolean',
        ]);

        try {
            Coupon::create([
                'code' => strtoupper(trim($request->code)),
                'discount_id' => $request->discount_id,
                'usage_limit' => $request->usage_limit ?: null,
                'used_count' => 0,
                'expires_at' => $request->expires_at ? $request->expires_at . ' 23:59:59' : null,
                'status' => $request->status,
            ]);

            toastr()->success('Coupon code created successfully!');
            return redirect()->route('admin.coupons.index');
        } catch (\Exception $e) {
            toastr()->error('Failed to create coupon: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function edit(Coupon $coupon): View
    {
        $discounts = Discount::latest()->get();
        return view('backend.coupons.edit', compact('coupon', 'discounts'));
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_id' => 'required|exists:discounts,id',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'status' => 'required|boolean',
        ]);

        try {
            $coupon->update([
                'code' => strtoupper(trim($request->code)),
                'discount_id' => $request->discount_id,
                'usage_limit' => $request->usage_limit ?: null,
                'expires_at' => $request->expires_at ? $request->expires_at . ' 23:59:59' : null,
                'status' => $request->status,
            ]);

            toastr()->success('Coupon code updated successfully!');
            return redirect()->route('admin.coupons.index');
        } catch (\Exception $e) {
            toastr()->error('Failed to update coupon: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return response(['status' => 'success', 'message' => 'Coupon deleted successfully!']);
        } catch (\Exception $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete coupon: ' . $e->getMessage()], 500);
        }
    }

    public function changeStatus(Request $request): JsonResponse
    {
        $coupon = Coupon::findOrFail($request->id);
        $coupon->status = $request->status == 'true' ? 1 : 0;
        $coupon->save();

        return response()->json(['status' => 'success', 'message' => 'Coupon status updated successfully!']);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->where('status', 1)->first();

        if (!$coupon) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or inactive coupon code.'], 404);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'This coupon code has expired.'], 422);
        }

        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['status' => 'error', 'message' => 'This coupon code has reached its maximum usage limit.'], 422);
        }

        $discount = $coupon->discount;
        if (!$discount) {
            return response()->json(['status' => 'error', 'message' => 'Discount configuration not found.'], 404);
        }

        $orderAmount = (float) $request->order_amount;
        $discountAmount = 0.00;

        if ($discount->discount_type === 'percent') {
            $discountAmount = $orderAmount * ($discount->discount_value / 100);
        } else {
            $discountAmount = min($orderAmount, (float) $discount->discount_value);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Coupon applied successfully!',
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount_type' => $discount->discount_type,
            'discount_value' => (float) $discount->discount_value,
            'calculated_discount' => round($discountAmount, 2),
        ]);
    }
}
