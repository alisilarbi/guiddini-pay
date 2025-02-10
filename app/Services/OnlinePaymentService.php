<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OnlinePaymentService
{

    public function execute(Request $request)
    {
        $request->validate([
            'pack_name' => 'required',
            'price' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'client_order_id' => 'required',
        ]);

        $transaction = Transaction::create([
            'pack_name' => $request->pack_name,
            'price' => $request->price,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'client_order_id' => $request->client_order_id,
        ]);

        $paymentGatewayUrl = "https://test.satim.dz/payment/rest/register.do";

        $username = 'SAT2405190928';
        $password = 'satim120';

        $orderId = $transaction->client_order_id;

        $returnUrl = route('confirm') . '?orderNumber=' . $orderId . '&bool=0';

        $jsonParams = json_encode([
            "orderNumber" => $orderId,
            "udf1" => $orderId,
            "udf5" => "00",
            "force_terminal_id" => "E010901161"
        ]);

        $response = Http::timeout(60)
            ->withOptions(['verify' => true])
            ->asForm()
            ->post($paymentGatewayUrl, [
                "userName" => $username,
                "password" => $password,
                "returnUrl" => $returnUrl,
                "orderNumber" => $orderId,
                "amount" => $request->price ,
                "currency" => "012",
                "jsonParams" => $jsonParams
            ]);

        $result = $response->json();

        return $result;

        if (isset($result['errorCode']) && strval($result['errorCode']) === "0") {
            return redirect($result['formUrl']);
        } else {
            $errorMessage = $result['errorMessage'] ?? 'Unknown error';
            return redirect("https://efawtara.com/?MessageReturn=" . urlencode($errorMessage));
        }
    }
    // public function execute(Request $request)
    // {
    //     $request->validate([
    //         'pack_name' => 'required',
    //         'price' => 'required',
    //         'name' => 'required',
    //         'email' => 'required',
    //         'phone' => 'required',
    //         'client_order_id' => 'required',
    //     ]);

    //     $transaction = Transaction::create([
    //         'pack_name' => $request->pack_name,
    //         'price' => $request->price,
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'phone' => $request->phone,
    //         'client_order_id' => $request->client_order_id,
    //     ]);

    //     $paymentGatewayUrl = "https://test.satim.dz/payment/rest/register.do";

    //     $username = 'SAT2405190928';
    //     $password = 'satim120';

    //     $orderId = $transaction->client_order_id;
    //     $returnUrl = route('confirm', ['orderNumber' => $orderId, 'bool' => 0]);

    //     $jsonParams = json_encode([
    //         "orderNumber" => $orderId,
    //         "udf1" => $orderId,
    //         "udf5" => "00",
    //         "force_terminal_id" => "E010901161"
    //     ]);

    //     $response = Http::asForm()->post($paymentGatewayUrl, [
    //         "sslverify" => "true",
    //         "timeout" => 60,
    //         "userName" => $username,
    //         "password" => $password,
    //         "returnUrl" => $returnUrl,
    //         "orderNumber" => $orderId,
    //         "amount" => $request->price * 100,
    //         "currency" => "012",
    //         "jsonParams" => $jsonParams
    //     ]);

    //     $result = $response->json();

    //     return $result;

    //     if (isset($result['errorCode']) && strval($result['errorCode']) === "0") {
    //         return redirect($result['formUrl']);
    //     } else {
    //         return redirect("https://efawtara.com/?MessageReturn=" . ($result['errorMessage'] ?? 'Unknown error'));
    //     }
    // }

    public function confirm()
    {
        dd('you finally got here');
    }
}
