<?php
// 10. CouponController.php
namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Coupon::query();

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            if ($request->has('valid')) {
                $query->where('expiry_date', '>=', now())
                    ->where('is_active', true);
            }

            $coupons = $query->orderBy('created_at', 'desc')->get();
            return response()->json($coupons);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch coupons', 'details' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create coupon', 'details' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            return response()->json($coupon);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Coupon not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch coupon', 'details' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Coupon not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update coupon', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();
            return response()->json(['message' => 'Coupon deleted successfully']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Coupon not found'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete coupon', 'details' => $e->getMessage()], 500);
        }
    }

    public function validate(Request $request)
    {
        try {
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
                if ($coupon->max_discount_amount) {
                    $discount = min($discount, $coupon->max_discount_amount);
                }
            }

            return response()->json([
                'valid' => true,
                'coupon' => $coupon,
                'discount' => (float) $discount
            ]);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to validate coupon', 'details' => $e->getMessage()], 500);
        }
    }
}
