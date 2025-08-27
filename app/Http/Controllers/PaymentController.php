<?php
// 11. PaymentController.php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
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

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($payments);
    }

    public function store(Request $request)
    {
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

        // Kiểm tra xem đã có payment completed chưa
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
    }

    public function show($id)
    {
        $payment = Payment::with(['order.user'])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && $payment->order->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($payment);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed',
            'transaction_id' => 'nullable|string|max:100'
        ]);

        $payment = Payment::findOrFail($id);

        if ($request->status === 'completed') {
            // Kiểm tra xem đã có payment completed khác chưa
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
    }
}