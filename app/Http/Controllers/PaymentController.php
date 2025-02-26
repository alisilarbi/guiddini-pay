<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\PaymentService;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\ApiResponseResource;

class PaymentController extends Controller
{
    use HandlesApiExceptions;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiate(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:50|decimal:0,2',
            ]);

            $result = $this->paymentService->initiatePayment(
                $validated,
                $request->header('X-App-Key')
            );

            return new ApiResponseResource([
                'success' => true,
                'code' => 'PAYMENT_INITIATED',
                'message' => 'Payment initiated successfully',
                'data' => $result,
                'http_code' => 201
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }


    public function confirm(Request $request, string $orderId)
    {

        try {
            $result = $this->paymentService->confirmPayment($orderId);
            dd($result);

            $redirectUrl = $result['transaction']['application']['success_redirect_url'];
            dd($redirectUrl);
            $queryParams = http_build_query($result['gateway_response']);

            return redirect()->to("$redirectUrl?$queryParams");
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
