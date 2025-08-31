<?php
// PaymentController.php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['order.user']);

            if (Auth::user()->role !== 'admin') {
                $query->whereHas('order', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('method')) {
                $query->where('method', $request->input('method'));
            }

            if ($request->has('order_id')) {
                $query->where('order_id', $request->order_id);
            }

            $payments = $query->orderBy('created_at', 'desc')->get();
            return response()->json($payments);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch payments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:0',
                'method' => 'required|in:cash,credit_card,bank_transfer,paypal',
                'transaction_id' => 'nullable|string|max:100'
            ]);

            $order = Order::findOrFail($request->order_id);

            if (Auth::user()->role !== 'admin' && $order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $existingPayment = Payment::where('order_id', $request->order_id)
                ->where('status', 'completed')
                ->first();

            if ($existingPayment) {
                return response()->json(['error' => 'Order already has a completed payment'], 400);
            }

            $payment = Payment::create([
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'method' => $request->input('method'),
                'status' => 'pending',
                'transaction_id' => $request->transaction_id
            ]);

            return response()->json($payment->load('order'), 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create payment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with(['order.user'])->findOrFail($id);

            if (Auth::user()->role !== 'admin' && $payment->order->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch payment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,completed,failed',
                'transaction_id' => 'nullable|string|max:100'
            ]);

            $payment = Payment::findOrFail($id);

            if ($request->status === 'completed') {
                $existingPayment = Payment::where('order_id', $payment->order_id)
                    ->where('status', 'completed')
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingPayment) {
                    return response()->json(['error' => 'Order already has a completed payment'], 400);
                }
            }

            $payment->update([
                'status' => $request->status,
                'transaction_id' => $request->transaction_id
            ]);

            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update payment status',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
