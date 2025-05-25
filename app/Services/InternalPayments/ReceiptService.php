<?php

namespace App\Services\InternalPayments;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use App\Exceptions\ReceiptException;
use Illuminate\Support\Facades\Mail;
use App\Mail\Partner\TransactionReceipt;


class ReceiptService
{
    public function downloadPaymentReceipt(string $orderNumber)
    {
        $transaction = Transaction::where('order_number', $orderNumber)->firstOrFail();
        $pdf = $this->generatePdf($transaction);

        return $pdf->download('invoice.pdf');
    }

    private function generatePdf(Transaction $transaction)
    {

        $guiddiniIconPath = public_path('images/icon_guiddinipay_dark.png');
        $guiddiningIconBase64 = file_exists($guiddiniIconPath) ? base64_encode(file_get_contents($guiddiniIconPath)) : null;

        $greenNumberLogoPath = public_path('images/green_number.png');
        $greenNumberLogoBase64 = file_exists($greenNumberLogoPath) ? base64_encode(file_get_contents($greenNumberLogoPath)) : null;

        $data = [
            'transaction' => $transaction,
            'greenNumberLogo' => $greenNumberLogoBase64,
            'guiddiniIcon' => $guiddiningIconBase64,
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

        return URL::signedRoute('internal.payment.pdf', ['order_number' => $orderNumber]);
    }

    public function emailPaymentReceipt(array $data): array
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

        $transaction = Transaction::where('order_number', $orderNumber)->first()
            ?: throw new ReceiptException(
                'Transaction not found',
                'TRANSACTION_NOT_FOUND',
                404
            );

        $transaction = Transaction::where('order_number', $orderNumber)->firstOrFail();
        $pdf = $this->generatePdf($transaction);

        $receiptUrl = $this->generateDownloadLink($transaction->order_number);

        Mail::to($email)->send(new TransactionReceipt($transaction, $pdf, $receiptUrl));

        return [
            'message' => 'Email sent successfully'
        ];
    }
}
