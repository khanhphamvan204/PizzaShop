<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VnPayController extends Controller
{
    /**
     * Create a VNPAY payment URL and redirect the user to VNPAY sandbox.
     * Expected input: order_id (optional), amount (VND, integer), description (optional)
     */
    public function pay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.vnp_url');
        $vnp_Returnurl = config('vnpay.return_url');

        $vnp_TxnRef = $request->input('order_id', time());
        $vnp_OrderInfo = $request->input('description', 'Thanh toan don hang ' . $vnp_TxnRef);
        $vnp_Amount = $request->input('amount') * 100; // VNPAY expects amount in cents (multiply by 100)
        $vnp_Locale = $request->input('locale', 'vn');
        $vnp_IpAddr = $request->ip();

        $vnp_Params = [];
        $vnp_Params['vnp_Version'] = '2.1.0';
        $vnp_Params['vnp_Command'] = 'pay';
        $vnp_Params['vnp_TmnCode'] = $vnp_TmnCode;
        $vnp_Params['vnp_Amount'] = $vnp_Amount;
        $vnp_Params['vnp_CurrCode'] = 'VND';
        $vnp_Params['vnp_TxnRef'] = $vnp_TxnRef;
        $vnp_Params['vnp_OrderInfo'] = $vnp_OrderInfo;
        $vnp_Params['vnp_OrderType'] = 'other';
        $vnp_Params['vnp_ReturnUrl'] = $vnp_Returnurl;
        $vnp_Params['vnp_IpAddr'] = $vnp_IpAddr;
        $vnp_Params['vnp_Locale'] = $vnp_Locale;
        $vnp_Params['vnp_CreateDate'] = date('YmdHis');
        // sort params by key
        ksort($vnp_Params);

        $query = [];
        $hashdata = '';
        foreach ($vnp_Params as $key => $value) {
            if ($hashdata !== '') {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
            }
            $query[] = urlencode($key) . '=' . urlencode($value);
        }

        // create secure hash
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $vnp_Url .= '?' . implode('&', $query) . '&vnp_SecureHash=' . $vnpSecureHash;

        // Create a Payment record (pending) so we can update it when VNPAY returns
        try {
            $payment = Payment::create([
                'order_id' => $request->input('order_id'),
                'amount' => $request->input('amount'),
                'method' => 'vnpay',
                'status' => 'pending',
                // store vnp txn ref temporarily in transaction_id to link callback
                'transaction_id' => $vnp_TxnRef,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Payment record before redirect to VNPAY', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create payment record'], 500);
        }

        // Redirect the customer to VNPAY
        return redirect()->away($vnp_Url);
    }

    /**
     * Handle return from VNPAY (customer redirected back).
     * This will validate the secure hash and show result.
     */
    public function return(Request $request)
    {
        $input = $request->all();
        $vnp_HashSecret = config('vnpay.hash_secret');

        // extract secure hash
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? null;

        // remove vnp_SecureHash from parameters for recomputing
        if (isset($input['vnp_SecureHash'])) {
            unset($input['vnp_SecureHash']);
        }
        if (isset($input['vnp_SecureHashType'])) {
            unset($input['vnp_SecureHashType']);
        }

        ksort($input);
        $hashdata = '';
        foreach ($input as $key => $value) {
            if ($hashdata !== '') {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
            }
        }

        $secureHashGen = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $valid = ($secureHashGen === $vnp_SecureHash);

        // Try to find Payment by vnp_TxnRef (we saved it in transaction_id when creating)
        $vnp_TxnRef = $input['vnp_TxnRef'] ?? null;
        $payment = null;
        if ($vnp_TxnRef) {
            $payment = Payment::where('transaction_id', $vnp_TxnRef)
                ->orWhere('order_id', $vnp_TxnRef)
                ->first();
        }

        if (! $valid) {
            Log::warning('VNPAY return: invalid secure hash', ['input' => $request->all()]);
            // update payment as failed if found
            if ($payment) {
                $payment->update(['status' => 'failed']);
            }
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        $responseCode = $input['vnp_ResponseCode'] ?? null;
        // prefer bank's transaction id if provided
        $bankTranId = $input['vnp_TransactionNo'] ?? ($input['vnp_TransNo'] ?? ($input['vnp_BankTranNo'] ?? null));

        if ($responseCode === '00') {
            // success
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $bankTranId ?? $payment->transaction_id,
                ]);
                // Optionally: update related order status here
                try {
                    if ($payment->order) {
                        $payment->order->update(['status' => 'paid']);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update order status after payment return', ['error' => $e->getMessage()]);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Payment successful', 'data' => $input]);
        }

        // not successful
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'transaction_id' => $bankTranId ?? $payment->transaction_id,
            ]);
        }

        return response()->json(['status' => 'failed', 'message' => 'Payment failed or cancelled', 'data' => $input]);
    }

    /**
     * IPN / notification endpoint: server-to-server notification from VNPAY.
     * VNPAY sandbox may POST/GET to this URL. We verify signature and update payment.
     */
    public function ipn(Request $request)
    {
        $input = $request->all();
        $vnp_HashSecret = config('vnpay.hash_secret');

        $vnp_SecureHash = $input['vnp_SecureHash'] ?? null;
        if (isset($input['vnp_SecureHash'])) {
            unset($input['vnp_SecureHash']);
        }
        if (isset($input['vnp_SecureHashType'])) {
            unset($input['vnp_SecureHashType']);
        }

        ksort($input);
        $hashdata = '';
        foreach ($input as $key => $value) {
            if ($hashdata !== '') {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
            }
        }

        $secureHashGen = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $valid = ($secureHashGen === $vnp_SecureHash);

        $vnp_TxnRef = $input['vnp_TxnRef'] ?? null;
        $payment = null;
        if ($vnp_TxnRef) {
            $payment = Payment::where('transaction_id', $vnp_TxnRef)
                ->orWhere('order_id', $vnp_TxnRef)
                ->first();
        }

        if (! $valid) {
            Log::warning('VNPAY IPN: invalid secure hash', ['input' => $request->all()]);
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $responseCode = $input['vnp_ResponseCode'] ?? null;
        $bankTranId = $input['vnp_TransactionNo'] ?? ($input['vnp_TransNo'] ?? ($input['vnp_BankTranNo'] ?? null));

        if ($responseCode === '00') {
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'transaction_id' => $bankTranId ?? $payment->transaction_id,
                ]);
            }
            // respond with code 00 for success
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        }

        if ($payment) {
            $payment->update(['status' => 'failed', 'transaction_id' => $bankTranId ?? $payment->transaction_id]);
        }

        return response()->json(['RspCode' => '01', 'Message' => 'Payment failed']);
    }

    /**
     * Initiate VNPAY from API (returns redirect URL) — no CSRF required because this is an API endpoint.
     * Expected POST body: amount (VND), order_id (optional), description (optional)
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.vnp_url');
        $vnp_Returnurl = config('vnpay.return_url');

        $vnp_TxnRef = $request->input('order_id', time());
        $vnp_OrderInfo = $request->input('description', 'Thanh toan don hang ' . $vnp_TxnRef);
        $vnp_Amount = $request->input('amount') * 100; // multiply by 100 per VNPAY expectation
        $vnp_Locale = $request->input('locale', 'vn');
        $vnp_IpAddr = $request->ip();

        $vnp_Params = [];
        $vnp_Params['vnp_Version'] = '2.1.0';
        $vnp_Params['vnp_Command'] = 'pay';
        $vnp_Params['vnp_TmnCode'] = $vnp_TmnCode;
        $vnp_Params['vnp_Amount'] = $vnp_Amount;
        $vnp_Params['vnp_CurrCode'] = 'VND';
        $vnp_Params['vnp_TxnRef'] = $vnp_TxnRef;
        $vnp_Params['vnp_OrderInfo'] = $vnp_OrderInfo;
        $vnp_Params['vnp_OrderType'] = 'other';
        $vnp_Params['vnp_ReturnUrl'] = $vnp_Returnurl;
        $vnp_Params['vnp_IpAddr'] = $vnp_IpAddr;
        $vnp_Params['vnp_Locale'] = $vnp_Locale;
        $vnp_Params['vnp_CreateDate'] = date('YmdHis');

        ksort($vnp_Params);
        $query = [];
        $hashdata = '';
        foreach ($vnp_Params as $key => $value) {
            if ($hashdata !== '') {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
            }
            $query[] = urlencode($key) . '=' . urlencode($value);
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $url = $vnp_Url . '?' . implode('&', $query) . '&vnp_SecureHash=' . $vnpSecureHash;

        // create Payment record (pending) so it can be updated later
        try {
            Payment::create([
                'order_id' => $request->input('order_id'),
                'amount' => $request->input('amount'),
                'method' => 'vnpay',
                'status' => 'pending',
                'transaction_id' => $vnp_TxnRef,
            ]);
        } catch (\Exception $e) {
            Log::warning('VNPAY initiate: failed to create payment record', ['error' => $e->getMessage()]);
            // still return URL, but caller should be aware
        }

        return response()->json(['url' => $url]);
    }
}
