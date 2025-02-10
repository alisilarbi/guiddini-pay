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
        try {
            $validated = $request->validate([
                'pack_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:50',
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
            ]);

            $result = $this->paymentService->initiatePayment(
                $validated,
                $request->header('X-App-Key')
            );

            dd($result); // Debug output

            // Only reached if dd is removed
            return response()->json($result);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            report($e);
            return back()->withErrors(['payment' => 'Payment processing error']);
        }
    }

    public function confirm(Request $request, $clientOrderId)
    {
        try {
            $result = $this->paymentService->confirmPayment(
                $clientOrderId,
                $request->header('X-App-Key')
            );

            dd($result); // Debug output

            return response()->json($result);
        } catch (\Exception $e) {
            dd([
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ]);
        }
    }

    public function failed($clientOrderId)
    {
        $transaction = Transaction::with('application')
            ->where('client_order_id', $clientOrderId)
            ->first();

        dd([
            'transaction' => $transaction,
            'gateway_data' => $transaction?->confirmation_response
        ]);
    }
}
