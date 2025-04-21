<?php

namespace App\Services\Payments;

use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Mail;
use App\Mail\User\TransactionReceipt;

class ReceiptService
{

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

    public function generateDownloadLink(string $orderNumber)
    {
        $signedUrl = URL::signedRoute('client.payment.pdf', ['order_number' => $orderNumber]);

        return $signedUrl;
    }

    public function emailPaymentReceipt(array $data, Application $application)
    {
        $orderNumber = $data['orderNumber'];
        $email = $data['email'];

        $transaction = Transaction::where('order_number', $orderNumber)->first();

        // $receiptUrl = URL::signedRoute('client.payment.pdf', ['order_number' => $transaction->order_number]);
        $receiptUrl = $this->generateDownloadLink($transaction->order_number);
        Mail::to($email)->send(new TransactionReceipt($transaction, $application, $receiptUrl));

        return [
            'message' => 'Email send successfully'
        ];
    }
}
