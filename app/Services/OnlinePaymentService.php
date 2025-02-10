<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OnlinePaymentService
{

    public function execute(Request $request)
    {
        $request->validate([
            'pack_name' => 'required',
            'price' => 'required|numeric|min:50',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
        ]);


        $maxOrderId = Transaction::max('client_order_id') ? Transaction::max('client_order_id') + 1 : 562000;
        $transaction = Transaction::create([
            'pack_name' => $request->pack_name,
            'price' => $request->price,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'client_order_id' => $maxOrderId,
        ]);

        $gatewayUrl = "https://test.satim.dz/payment/rest/register.do";
        $application = Application::where('app_key', $request->header('x-app-key'))->first();
        $credentials = [
            'userName' => $application->username,
            'password' => $application->password,
            'terminal_id' => $application->terminal,
        ];

        $params = [
            'orderNumber' => $transaction->client_order_id,
            'amount' => $request->price * 100,
            'currency' => '012',
            'returnUrl' => route('payment.confirm', ['order_id' => $transaction->id]),
            'failUrl' => route('payment.failed'),
            'language' => 'EN', //
            'jsonParams' => json_encode([
                "force_terminal_id" => $credentials['terminal_id'],
                "udf1" => $transaction->client_order_id,
                "udf5" => "00"
            ])
        ];

        $fullParams = array_merge($credentials, $params);

        try {
            $response = Http::timeout(30)
                ->post($gatewayUrl, $fullParams);

            $result = $response->json();
            dd($result);

            Log::debug('SATIM Payment Response', $result);

            if ($response->successful() && isset($result['errorCode']) && $result['errorCode'] == 0) {
                return redirect()->away($result['formUrl']);
            }

            $errorMessage = $result['errorMessage'] ?? 'Unknown payment gateway error';
            return back()->withErrors(['payment' => $errorMessage]);
        } catch (\Exception $e) {

            dd($e->getMessage());
            Log::error('Payment Gateway Error: ' . $e->getMessage());
            return back()->withErrors(['payment' => 'Connection to payment gateway failed']);
        }
    }

    // Payment confirmation handler
    public function confirm(Request $request)
    {
        $orderId = $request->input('orderId');

        // Verify payment status
        $verificationResponse = Http::get('https://test.satim.dz/payment/rest/confirmOrder.do', [
            'userName' => config('services.satim.username'),
            'password' => config('services.satim.password'),
            'orderId' => $orderId,
            'language' => 'EN'
        ]);

        // Process verification response
        if ($verificationResponse->successful()) {
            $verificationData = $verificationResponse->json();
            // Update transaction status based on $verificationData
            // ...
        }

        return view('payment.confirmation');
    }
}
