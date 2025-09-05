<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\QueryException;
use Exception;

class RevenueController extends Controller
{
    /**
     * 1. THỐNG KÊ DOANH THU THEO THỜI GIAN
     */

    // Doanh thu theo ngày
    public function dailyRevenue(Request $request)
    {
        try {
            $dateInput = $request->input('date', Carbon::today()->format('d/m/Y'));
            $date = Carbon::createFromFormat('d/m/Y', $dateInput)->format('Y-m-d');

            $result = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->where('payments.status', 'completed')
                ->whereDate('orders.created_at', $date)
                ->select(
                    DB::raw('DATE_FORMAT(orders.created_at, "%d/%m/%Y") as date'),
                    DB::raw('SUM(payments.amount) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    DB::raw('AVG(payments.amount) as avg_order_value')
                )
                ->groupBy(DB::raw('DATE_FORMAT(orders.created_at, "%d/%m/%Y")')) // Sửa để khớp với SELECT
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (InvalidFormatException $e) {
            return response()->json(['error' => 'Invalid date format. Please use d/m/Y (e.g., 04/09/2025).'], 400);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    // Doanh thu theo tuần
    public function weeklyRevenue(Request $request)
    {
        try {
            $year = $request->input('year', Carbon::now()->isoWeekYear);
            $week = $request->input('week', Carbon::now()->isoWeek);

            if (!is_numeric($year) || !is_numeric($week) || $week < 1 || $week > 53) {
                throw new Exception('Invalid year or week number.');
            }

            $yearWeekValue = $year . str_pad($week, 2, '0', STR_PAD_LEFT);

            $result = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->where('payments.status', 'completed')
                ->where(DB::raw('YEARWEEK(orders.created_at, 3)'), $yearWeekValue)
                ->select(
                    DB::raw('WEEK(orders.created_at, 3) as week'),
                    DB::raw('YEAR(orders.created_at) as year'),
                    DB::raw('SUM(payments.amount) as total_revenue'),
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders')
                )
                // SỬA ĐỔI DUY NHẤT TẠI ĐÂY
                ->groupBy(DB::raw('YEAR(orders.created_at)'), DB::raw('WEEK(orders.created_at, 3)'))
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid input: ' . $e->getMessage()], 400);
        }
    }

    // Doanh thu theo tháng
    public function monthlyRevenue(Request $request)
    {
        try {
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month');

            if (!is_numeric($year)) {
                throw new Exception('Invalid year format.');
            }

            if ($month !== null && (!is_numeric($month) || $month < 1 || $month > 12)) {
                throw new Exception('Invalid month format. Month must be a number between 1 and 12.');
            }

            $query = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->where('payments.status', 'completed')
                ->whereYear('orders.created_at', $year);

            // Nếu month được cung cấp, thêm điều kiện lọc theo tháng
            if ($month !== null) {
                $query->whereMonth('orders.created_at', $month);
            }

            $result = $query->select(
                DB::raw('MONTH(orders.created_at) as month'),
                DB::raw('YEAR(orders.created_at) as year'),
                DB::raw('DATE_FORMAT(orders.created_at, "%m/%Y") as month_name'),
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('AVG(payments.amount) as avg_order_value')
            )
                ->groupBy(
                    DB::raw('MONTH(orders.created_at)'),
                    DB::raw('YEAR(orders.created_at)'),
                    DB::raw('DATE_FORMAT(orders.created_at, "%m/%Y")')
                )
                ->orderBy(DB::raw('MONTH(orders.created_at)'))
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (InvalidFormatException $e) {
            return response()->json(['error' => 'Invalid date format. Please use d/m/Y (e.g., 04/09/2025).'], 400);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid input: ' . $e->getMessage()], 400);
        }
    }

    // Doanh thu theo năm
    public function yearlyRevenue(Request $request)
    {
        try {
            $year = $request->input('year');

            if ($year !== null && (!is_numeric($year) || $year < 1900 || $year > 9999)) {
                throw new Exception('Invalid year format. Year must be a number between 1900 and 9999.');
            }

            $query = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->where('payments.status', 'completed');
            // ->where('orders.status', 'delivered'); // Thêm điều kiện để khớp với trạng thái đơn hàng

            // Nếu year được cung cấp, thêm điều kiện lọc theo năm
            if ($year !== null) {
                $query->whereYear('orders.created_at', $year);
            }

            $result = $query->select(
                DB::raw('YEAR(orders.created_at) as year'),
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('AVG(payments.amount) as avg_order_value')
            )
                ->groupBy(DB::raw('YEAR(orders.created_at)'))
                ->orderBy(DB::raw('YEAR(orders.created_at)'))
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid input: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 2. THỐNG KÊ DOANH THU THEO SẢN PHẨM
     */

    // Doanh thu theo danh mục
    // public function revenueByCategory(Request $request)
    // {
    //     try {
    //         $startDateInput = $request->input('start_date');
    //         $endDateInput = $request->input('end_date');
    //         $category = $request->input('category');
    //         $startDate = $startDateInput ? Carbon::createFromFormat('d/m/Y', $startDateInput)->format('Y-m-d') : null;
    //         $endDate = $endDateInput ? Carbon::createFromFormat('d/m/Y', $endDateInput)->format('Y-m-d') : null;

    //         $query = DB::table('order_items')
    //             ->join('orders', 'order_items.order_id', '=', 'orders.id')
    //             ->join('payments', 'orders.id', '=', 'payments.order_id')
    //             ->leftJoin('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
    //             ->leftJoin('products', 'product_variants.product_id', '=', 'products.id')
    //             ->leftJoin('categories as product_categories', 'products.category_id', '=', 'product_categories.id')
    //             ->leftJoin('combos', 'order_items.combo_id', '=', 'combos.id')
    //             ->where('payments.status', 'completed');


    //         if ($startDate) {
    //             $query->whereDate('orders.created_at', '>=', $startDate);
    //         }
    //         if ($endDate) {
    //             $query->whereDate('orders.created_at', '<=', $endDate);
    //         }

    //         if ($category !== null) {
    //             if (is_numeric($category)) {
    //                 $query->where(function ($q) use ($category) {
    //                     $q->where('product_categories.id', $category)
    //                         ->orWhere('combos.id', $category);
    //                 });
    //             } else {
    //                 $query->where(function ($q) use ($category) {
    //                     $q->where('product_categories.name', $category)
    //                         ->orWhere('combos.name', $category);
    //                 });
    //             }
    //         }

    //         $result = $query->select(
    //             DB::raw('
    //         CASE
    //             WHEN product_categories.name IS NOT NULL THEN product_categories.name
    //             ELSE CONCAT("Combo: ", combos.name)
    //         END as category_name
    //     '),
    //             DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
    //             DB::raw('SUM(order_items.quantity) as total_quantity'),
    //             DB::raw('COUNT(DISTINCT orders.id) as total_orders')
    //         )
    //             ->groupBy(DB::raw('
    //     CASE
    //         WHEN product_categories.id IS NOT NULL THEN product_categories.id
    //         ELSE combos.id
    //     END,
    //     CASE
    //         WHEN product_categories.name IS NOT NULL THEN product_categories.name
    //         ELSE CONCAT("Combo: ", combos.name)
    //     END
    // '))
    //             ->orderBy('total_revenue', 'DESC')
    //             ->get();


    //         return response()->json(['data' => $result], 200);
    //     } catch (InvalidFormatException $e) {
    //         return response()->json(['error' => 'Invalid date format. Please use d/m/Y (e.g., 04/09/2025).'], 400);
    //     } catch (QueryException $e) {
    //         return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
    //     } catch (Exception $e) {
    //         return response()->json(['error' => 'Invalid input: ' . $e->getMessage()], 400);
    //     }
    // }

    // Top sản phẩm bán chạy
    public function topSellingProducts(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);

            if (!is_numeric($limit) || $limit <= 0) {
                throw new Exception('Invalid limit value.');
            }

            $result = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->join('product_variants', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->leftJoin('sizes', 'product_variants.size_id', '=', 'sizes.id')
                ->leftJoin('crusts', 'product_variants.crust_id', '=', 'crusts.id')
                ->where('payments.status', 'completed')
                ->select(
                    'products.name as product_name',
                    'sizes.name as size_name',
                    'crusts.name as crust_name',
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                    DB::raw('AVG(order_items.price) as avg_price')
                )
                ->groupBy('product_variants.id', 'products.name', 'sizes.name', 'crusts.name')
                ->orderBy('total_revenue', 'DESC')
                ->limit($limit)
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid input: ' . $e->getMessage()], 400);
        }
    }

    // Doanh thu combo
    public function comboRevenue(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            if (!is_numeric($limit) || $limit <= 0) {
                throw new Exception('Invalid limit value.');
            }

            $result = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->join('combos', 'order_items.combo_id', '=', 'combos.id')
                ->where('payments.status', 'completed')
                ->where('orders.status', 'delivered')
                ->select(
                    DB::raw('REGEXP_REPLACE(COALESCE(combos.name, "Unknown"), "^Combo\\s+", "") as combo_name'),
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                    'combos.price as combo_price',
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders')
                )
                ->groupBy('combos.id', 'combos.name', 'combos.price')
                ->orderBy('total_revenue', 'DESC')
                ->limit($limit)
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 3. THỐNG KÊ DOANH THU THEO KHÁCH HÀNG
     */

    // Top khách hàng VIP
    public function topCustomers(Request $request)
    {
        try {
            $limit = $request->input('limit', 20);

            if (!is_numeric($limit) || $limit <= 0) {
                throw new Exception('Invalid limit value.');
            }

            $result = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('payments.status', 'completed')
                ->select(
                    'users.full_name',
                    'users.email',
                    'users.phone',
                    DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                    DB::raw('SUM(payments.amount) as total_spent'),
                    DB::raw('AVG(payments.amount) as avg_order_value'),
                    DB::raw('DATE_FORMAT(MAX(orders.created_at), "%d/%m/%Y") as last_order_date')
                )
                ->groupBy('users.id', 'users.full_name', 'users.email', 'users.phone')
                ->orderBy('total_spent', 'DESC')
                ->limit($limit)
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid input: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 4. THỐNG KÊ DOANH THU THEO COUPON/KHUYẾN MÃI
     */

    public function revenueWithCoupons(Request $request)
    {
        try {
            $result = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->leftJoin('coupons', 'orders.coupon_id', '=', 'coupons.id')
                ->where('payments.status', 'completed')
                ->select(
                    DB::raw('COALESCE(coupons.code, "NO_COUPON") as coupon_code'),
                    'coupons.discount_percentage',
                    'coupons.discount_amount',
                    DB::raw('COUNT(DISTINCT orders.id) as usage_count'),
                    DB::raw('SUM(payments.amount) as total_revenue_after_discount'),
                    DB::raw('SUM(CASE 
                    WHEN coupons.discount_percentage IS NOT NULL 
                        THEN payments.amount / (1 - coupons.discount_percentage/100) - payments.amount
                    WHEN coupons.discount_amount IS NOT NULL 
                        THEN coupons.discount_amount
                    ELSE 0
                END) as total_discount_given')
                )
                ->groupBy(
                    DB::raw('COALESCE(coupons.id, orders.id)'),
                    'coupons.code',
                    'coupons.discount_percentage',
                    'coupons.discount_amount'
                )
                ->orderBy('usage_count', 'DESC')
                ->get();

            return response()->json(['data' => $result], 200);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }


    /**
     * 5. THỐNG KÊ TỔNG QUAN
     */

    public function dashboardStats(Request $request)
    {
        try {
            $startDateInput = $request->input('start_date');
            $endDateInput = $request->input('end_date');
            $startDate = $startDateInput ? Carbon::createFromFormat('d/m/Y', $startDateInput)->format('Y-m-d') : null;
            $endDate = $endDateInput ? Carbon::createFromFormat('d/m/Y', $endDateInput)->format('Y-m-d') : null;

            $query = DB::table('orders')
                ->join('payments', 'orders.id', '=', 'payments.order_id')
                ->where('payments.status', 'completed');

            if ($startDate) {
                $query->whereDate('orders.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('orders.created_at', '<=', $endDate);
            }

            $stats = $query->select(
                DB::raw('SUM(payments.amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('COUNT(DISTINCT orders.user_id) as unique_customers'),
                DB::raw('AVG(payments.amount) as avg_order_value'),
                DB::raw('MAX(payments.amount) as highest_order'),
                DB::raw('MIN(payments.amount) as lowest_order')
            )->first();

            $orderStats = DB::table('orders')
                ->select(
                    'status',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(total_amount) as total_amount')
                )
                ->groupBy('status')
                ->get();

            return response()->json([
                'data' => [
                    'overview' => $stats,
                    'order_status' => $orderStats
                ]
            ], 200);
        } catch (InvalidFormatException $e) {
            return response()->json(['error' => 'Invalid date format. Please use d/m/Y (e.g., 04/09/2025).'], 400);
        } catch (QueryException $e) {
            return response()->json(['error' => 'Database query failed: ' . $e->getMessage()], 500);
        } catch (Exception $e) {
            return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
        }
    }
}