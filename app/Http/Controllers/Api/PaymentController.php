<?php

namespace App\Http\Controllers\Api;

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

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService, private ReceiptService $receiptService) {}

    public function initiate(Request $request)
    {

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
    }

    private function getGatewayErrorCode(array $response): string
    {
        return (string)($response['ErrorCode'] ?? $response['errorCode'] ?? 'UNKNOWN');
    }

    public function getTransaction(Request $request)
    {

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
    }

    public function getPaymentReceipt(Request $request)
    {

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
    }

    public function downloadPaymentReceipt(string $orderNumber): \Illuminate\Http\Response
    {
        $transaction = Transaction::where('order_number', $orderNumber)->first();
        $application = $transaction->application;

        $guiddiniLogo = base64_encode(file_get_contents(public_path('images/icon.png')));
        $pdf = Pdf::loadView('components.pdfs.transaction-success', [
            'transaction' => $transaction,
            'application' => $application,
            'guiddiniLogo' => $guiddiniLogo,
        ])->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf->download('invoice.pdf');
    }

    public function emailPaymentReceipt(Request $request)
    {
        $request->validate([
            'order_number' => 'required',
            'email' => 'required',
        ]);

        $data = [
            'orderNumber' => $request->order_number,
            'email' => $request->email,
            'x-app-key' => $request->header('x-app-key'),
            'x-secret-key' => $request->header('x-secret-key'),
        ];

        dd($request->header('application'));

        $this->receiptService->emailPaymentReceipt($data);

        return response()->json([
            'data' => null,
            'meta' => [
                'code' => 'EMAIL_SENT',
                'message' => 'Email send successfully'
            ]
        ], 200);
    }
}
