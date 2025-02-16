<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'client_order_id' => 'required|string',
        ]);

        $result = $this->paymentService->initiatePayment(
            $validated,
            $request->header('X-App-Key')
        );

        dd($result['gateway_response']['formUrl']);

        if ($result['gateway_response']['errorCode'] == 0) {
            return redirect()->away($result['gateway_response']['formUrl']);
        }

        return back()->withErrors([
            'payment' => $result['gateway_response']['errorMessage'] ?? 'Payment initiation failed'
        ]);
    }

    public function confirm(Request $request, $clientOrderId)
    {
        try {
            $result = $this->paymentService->confirmPayment(
                $clientOrderId,
                $request->header('X-App-Key')
            );

            dd($result);

            if ($result['status'] === 'success') {

                return view('payment.success', [
                    'transaction' => $result['transaction'],
                    'response' => $result['gateway_response']
                ]);
            }

            return redirect()->route('payment.failed', $clientOrderId)
                ->withErrors(['confirm' => $result['message'] ?? 'Confirmation failed']);
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('payment.failed', $clientOrderId)
                ->withErrors(['confirm' => 'Payment confirmation error']);
        }
    }

    public function failed(Request $request, $clientOrderId)
    {
        try {

            $result = $this->paymentService->confirmPayment(
                $clientOrderId,
            );

            dd($result);

            return redirect()->route('payment.failed', $clientOrderId)
                ->withErrors(['confirm' => $result['message'] ?? 'Confirmation failed']);
        } catch (\Exception $e) {
            report($e);
            return redirect()->route('payment.failed', $clientOrderId)
                ->withErrors(['confirm' => 'Payment confirmation error']);
        }
    }
}
