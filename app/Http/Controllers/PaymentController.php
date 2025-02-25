<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\PaymentService;
use App\Http\Resources\ApiResponseResource;

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
            'amount' => 'required|numeric|min:50|decimal:0,2',
        ]);

        $result = $this->paymentService->initiatePayment(
            $validated,
            $request->header('X-App-Key')
        );

        return new ApiResponseResource($result);
    }

    public function confirm(Request $request)
    {
        $result = $this->paymentService->confirmPayment($request->orderId);
        dd($result);

        // $url = $result['transaction']->application->success_redirect_url;
        // $queryString = http_build_query($response);

        // return redirect()->to($url . '?' . $queryString);
    }

}
