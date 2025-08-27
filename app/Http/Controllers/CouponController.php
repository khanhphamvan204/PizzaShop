<?php
// 10. CouponController.php
namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('valid')) {
            $query->where('expiry_date', '>=', now())
                ->where('is_active', true);
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($coupons);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if (!$request->discount_percentage && !$request->discount_amount) {
            return response()->json(['error' => 'Either discount_percentage or discount_amount is required'], 400);
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

        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $id,
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        if (!$request->discount_percentage && !$request->discount_amount) {
            return response()->json(['error' => 'Either discount_percentage or discount_amount is required'], 400);
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

    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->where('expiry_date', '>=', now())
            ->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired coupon'], 400);
        }

        if ($coupon->min_order_amount && $request->order_amount < $coupon->min_order_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Minimum order amount required: ' . number_format($coupon->min_order_amount)
            ], 400);
        }

        $discount = 0;
        if ($coupon->discount_percentage) {
            $discount = ($request->order_amount * $coupon->discount_percentage) / 100;
            if ($coupon->max_discount_amount) {
                $discount = min($discount, $coupon->max_discount_amount);
            }
        } elseif ($coupon->discount_amount) {
            $discount = $coupon->discount_amount;
        }

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount
        ]);
    }
}