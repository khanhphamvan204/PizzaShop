<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('order.user')->get();
        return response()->json([
            'status' => 'success',
            'data' => $payments
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,credit_card,bank_transfer,paypal',
            'status' => 'required|in:pending,completed,failed',
            'transaction_id' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::create($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $payment->load('order.user')
        ], 201);
    }

    public function show(Payment $payment)
    {
        return response()->json([
            'status' => 'success',
            'data' => $payment->load('order.user')
        ], 200);
    }

    public function update(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,credit_card,bank_transfer,paypal',
            'status' => 'required|in:pending,completed,failed',
            'transaction_id' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment->update($request->all());
        return response()->json([
            'status' => 'success',
            'data' => $payment->load('order.user')
        ], 200);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Payment deleted successfully'
        ], 200);
    }
}