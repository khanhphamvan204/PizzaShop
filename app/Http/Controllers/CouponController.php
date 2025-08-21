<?php
namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with('orders')->get();
        return response()->json([
            'status' => 'success',
            'data' => $coupons
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expiry_date' => 'required|date',
            'min_order_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $coupon->load('orders')
        ], 201);
    }

    public function show(Coupon $coupon)
    {
        return response()->json([
            'status' => 'success',
            'data' => $coupon->load('orders')
        ], 200);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expiry_date' => 'required|date',
            'min_order_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $coupon->load('orders')
        ], 200);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Coupon deleted successfully'
        ], 200);
    }
}