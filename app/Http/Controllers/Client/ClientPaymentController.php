<?php

namespace App\Http\Controllers\Client;

use App\Models\User;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;

use App\Traits\HandlesApiExceptions;
use App\Traits\HandlesWebExceptions;
use Illuminate\Support\Facades\Mail;
use App\Mail\User\TransactionReceipt;
use App\Services\Payments\PaymentService;
use App\Services\Payments\ReceiptService;
use App\Http\Resources\Api\PaymentResource;
use App\Http\Resources\Api\TransactionResource;

class ClientPaymentController extends Controller
{
    use HandlesApiExceptions;
    use HandlesWebExceptions;

    public function __construct(private PaymentService $paymentService, private ReceiptService $receiptService) {}

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

            return new PaymentResource([
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

    public function confirm(string $orderNumber)
    {
        $result = $this->paymentService->confirmPayment($orderNumber);

        $transaction = $result['transaction'];
        $gatewayResponse = $result['gateway_response'];

        $redirectUrl = $transaction->application->redirect_url;

        $queryParams = http_build_query([
            'order_number' => $orderNumber,
            // 'status' => $transaction->status,
            // 'confirmation_status' => $transaction->confirmation_status,
            // 'gateway_code' => $this->getGatewayErrorCode($gatewayResponse)
        ]);

        if ($transaction->origin === 'System')
            return redirect()->route('certification', [
                'slug' => $transaction->application->slug,
                'order_number' => $transaction->order_number
            ]);

        return redirect()->to("$redirectUrl?$queryParams");

        try {
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    private function getGatewayErrorCode(array $response): string
    {
        return (string)($response['ErrorCode'] ?? $response['errorCode'] ?? 'UNKNOWN');
    }

    public function getTransaction(Request $request)
    {
        try {
            $request->validate([
                'order_number' => 'required',
            ]);

            $transaction = Transaction::where('order_number', $request->order_number)->firstOrFail();
            $receiptUrl = URL::signedRoute('client.payment.pdf', ['order_number' => $transaction->order_number]);

            return new TransactionResource([
                'success' => true,
                'code' => 'TRANSACTION_FOUND',
                'message' => 'Transaction retrieved successfully',
                'data' => [
                    'transaction' => $transaction->toArray(),
                    'receipt_url' => $receiptUrl,
                ],
                'http_code' => 200
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    public function getPaymentReceipt(Request $request)
    {
        try {

            $request->validate([
                'order_number' => 'required',
            ]);

            $transaction = Transaction::where('order_number', $request->order_number)->firstOrFail();
            $receiptUrl = URL::signedRoute('client.payment.pdf', ['order_number' => $transaction->order_number]);

            return response()->json([
                'links' => [
                    'self' => route('api.client.payment.receipt', ['order_number' => $request->order_number]) ?? null,
                    'href' => $receiptUrl,
                ],
                'meta' => [
                    'code' => 'RECEIPT_GENERATED',
                    'message' => 'Receipt generated successfully'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    public function downloadPaymentReceipt(string $orderNumber): \Illuminate\Http\Response
    {
        $transaction = Transaction::where('order_number', $orderNumber)->first();
        $application = $transaction->application;

        // $pdf = Pdf::loadView('components.pdfs.transaction-success', compact('transaction'));
        $pdf = Pdf::loadView('components.pdfs.transaction-success', [
            'transaction' => $transaction,
            'application' => $application,
        ]);

        return $pdf->download('invoice.pdf');
    }

    public function emailPaymentReceipt(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'required',
            'email' => 'required',
        ]);

        $appKey = $request->header('x-app-key');
        $secretKey = $request->header('x-secret-key');

        $application = Application::where('app_key', $appKey)
            ->where('app_secret', $secretKey)
            ->first();

            dd($data);

        $this->receiptService->emailPaymentReceipt($data, $application);

        return response()->json([
            'data' => null,
            'meta' => [
                'code' => 'EMAIL_SENT',
                'message' => 'Email send successfully'
            ]
        ], 200);
    }

    public function certification(string $slug)
    {
        $application = Application::where('slug', $slug)->firstOrFail();

        return view('public.user.payment')->with(['application' => $application]);
    }
}
