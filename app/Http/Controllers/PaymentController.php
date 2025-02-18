<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\PaymentService;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiate(Request $request)
    {
        dd('controller initiate');
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $result = $this->paymentService->initiatePayment(
            $validated,
            $request->header('X-App-Key')
        );


        if ($result['gateway_response']['errorCode'] == 0) {
            return response()->json($result, Response::HTTP_OK);
            // return redirect()->away($result['gateway_response']['formUrl']);
        }

        return back()->withErrors([
            'payment' => $result['gateway_response']['errorMessage'] ?? 'Payment initiation failed'
        ]);
    }

    public function confirm(Request $request)
    {
        dd('controller confirm');

        $result = $this->paymentService->confirmPayment(
            $clientOrderId,
            $request->header('X-App-Key')
        );


        if ($result['status'] === 'success') {

            return view('payment.success', [
                'transaction' => $result['transaction'],
                'response' => $result['gateway_response']
            ]);
        }

        return redirect()->route('payment.failed', $clientOrderId)
            ->withErrors(['confirm' => $result['message'] ?? 'Confirmation failed']);
    }

    public function failed(Request $request)
    {

        dd('controller failed');
        $result = $this->paymentService->confirmPayment(
            $request->orderId,
        );
    }
}
