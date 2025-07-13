<?php

namespace App\Services\Payments;

use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use App\Exceptions\PaymentException;
use App\Exceptions\ReceiptException;
use Illuminate\Support\Facades\Mail;
use App\Mail\User\TransactionReceipt;

class ReceiptService
{
    public function downloadPaymentReceipt(string $orderNumber)
    {
        $transaction = Transaction::where('order_number', $orderNumber)->firstOrFail();
        $application = $transaction->application;

        $pdf = $this->generatePdf($application, $transaction);

        return $pdf->download('invoice.pdf');
    }

    private function generatePdf(Application $application, Transaction $transaction){

        $guiddiniIconPath = public_path('images/icon_guiddinipay_dark.png');
        $guiddiningIconBase64 = file_exists($guiddiniIconPath) ? base64_encode(file_get_contents($guiddiniIconPath)) : null;

        $greenNumberLogoPath = public_path('images/green_number.png');
        $greenNumberLogoBase64 = file_exists($greenNumberLogoPath) ? base64_encode(file_get_contents($greenNumberLogoPath)) : null;

        $applicationLogoBase64 = null;
        if ($application->logo) {
            $applicationLogoPath = public_path($application->logo);
            $applicationLogoBase64 = file_exists($applicationLogoPath) ? base64_encode(file_get_contents($applicationLogoPath)) : null;
        }

        $data = [
            'transaction' => $transaction,
            'application' => $application,
            'greenNumberLogo' => $greenNumberLogoBase64,
            'applicationLogo' => $applicationLogoBase64,
            'guiddiniIcon' => $guiddiningIconBase64,
            'companyName' => $application->name,
            'phone' => $application->user->phone,
            'email' => $application->user->email,
            'paymentMethod' => 'CIB / Edahabia',
            'orderId' => $transaction->order_id ?? null,
            'orderNumber' => $transaction->order_number ?? null,
            'approvalCode' => $transaction->approval_code ?? null,
            'dateTime' => $transaction->updated_at ?? null,
            'amount' => $transaction->amount ?? 0.00,
        ];

        return Pdf::loadView('components.pdfs.transaction-success', $data)->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
    }

    public function generateDownloadLink(string $orderNumber): string
    {
        $transaction = Transaction::where('order_number', $orderNumber)->first()
            ?: throw new ReceiptException(
                'Transaction not found',
                'TRANSACTION_NOT_FOUND',
                404
            );

        return URL::signedRoute('client.payment.pdf', ['order_number' => $orderNumber]);
    }

    public function emailPaymentReceipt(array $data, Application $application): void
    {
        if (!isset($data['orderNumber']) || !isset($data['email'])) {
            throw new ReceiptException(
                'Missing required fields: orderNumber or email',
                'INVALID_INPUT',
                422
            );
        }

        $orderNumber = $data['orderNumber'];
        $email = $data['email'];

        $transaction = Transaction::where('order_number', $orderNumber)->firstOrFail();

        $transaction = Transaction::where('order_number', $orderNumber)->firstOrFail();
        $application = $transaction->application;

        $pdf = $this->generatePdf($application, $transaction);
        $receiptUrl = $this->generateDownloadLink($transaction->order_number);
        Mail::to($email)->send(new TransactionReceipt($transaction, $application, $pdf, $receiptUrl));
    }
}
