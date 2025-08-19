<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->get();
        return response()->json($coupons);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:coupons|max:50',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'min_order_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate that either discount_percentage or discount_amount is provided
        if (!$request->discount_percentage && !$request->discount_amount) {
            return response()->json(['error' => 'Either discount percentage or discount amount must be provided'], 422);
        }

        $coupon = Coupon::create($request->all());
        return response()->json($coupon, 201);
    }

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        return response()->json($coupon);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:coupons,code,'.$id.'|max:50',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expiry_date' => 'required|date',
            'min_order_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $coupon->update($request->all());
        return response()->json($coupon);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();
        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $coupon = Coupon::where('code', $request->code)
                        ->where('expiry_date', '>=', now()->toDateString())
                        ->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid or expired coupon'], 422);
        }

        if ($coupon->min_order_amount && $request->order_amount < $coupon->min_order_amount) {
            return response()->json(['error' => 'Minimum order amount not met'], 422);
        }

        $discountAmount = 0;
        if ($coupon->discount_percentage) {
            $discountAmount = ($request->order_amount * $coupon->discount_percentage) / 100;
        } elseif ($coupon->discount_amount) {
            $discountAmount = $coupon->discount_amount;
        }

        return response()->json([
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'final_amount' => max(0, $request->order_amount - $discountAmount)
        ]);
    }
}