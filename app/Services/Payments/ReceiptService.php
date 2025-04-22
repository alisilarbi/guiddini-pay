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
        try {
            $transaction = Transaction::where('order_number', $orderNumber)->first();

            if (!$transaction) {
                throw new PaymentException(
                    'Transaction not found',
                    'TRANSACTION_NOT_FOUND',
                    404
                );
            }

            $application = $transaction->application;
            $pdf = Pdf::loadView('components.pdfs.transaction-success', [
                'transaction' => $transaction,
                'application' => $application,
            ])
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                ]);

            return $pdf->download('invoice.pdf');
        } catch (\Exception $e) {
            if ($e instanceof PaymentException) {
                throw $e;
            }

            throw new PaymentException(
                'Failed to generate payment receipt',
                'RECEIPT_GENERATION_FAILED',
                500,
                ['error_details' => $e->getMessage()]
            );
        }
    }

    public function generateDownloadLink(string $orderNumber)
    {
        try {
            $transaction = Transaction::where('order_number', $orderNumber)->first();

            if (!$transaction) {
                throw new PaymentException(
                    'Transaction not found',
                    'TRANSACTION_NOT_FOUND',
                    404
                );
            }

            $signedUrl = URL::signedRoute('client.payment.pdf', ['order_number' => $orderNumber]);

            return $signedUrl;
        } catch (\Exception $e) {
            if ($e instanceof PaymentException) {
                throw $e;
            }

            throw new PaymentException(
                'Failed to generate download link',
                'DOWNLOAD_LINK_GENERATION_FAILED',
                500,
                ['error_details' => $e->getMessage()]
            );
        }
    }

    public function emailPaymentReceipt(array $data, Application $application)
    {
        try {
            $orderNumber = $data['orderNumber'];
            $email = $data['email'];

            $transaction = Transaction::where('order_number', $orderNumber)->first();

            if (!$transaction) {
                throw new PaymentException(
                    'Transaction not found',
                    'TRANSACTION_NOT_FOUND',
                    404
                );
            }

            $receiptUrl = $this->generateDownloadLink($transaction->order_number);
            Mail::to($email)->send(new TransactionReceipt($transaction, $application, $receiptUrl));

            return [
                'message' => 'Email sent successfully'
            ];
        } catch (\Exception $e) {
            if ($e instanceof PaymentException) {
                throw $e;
            }

            throw new PaymentException(
                'Failed to send payment receipt email',
                'EMAIL_SENDING_FAILED',
                500,
                ['error_details' => $e->getMessage()]
            );
        }
    }



    // public function downloadPaymentReceipt(string $orderNumber): \Illuminate\Http\Response
    // {
    //     $transaction = Transaction::where('order_number', $orderNumber)->first();
    //     $application = $transaction->application;

    //     $pdf = Pdf::loadView('components.pdfs.transaction-success', [
    //         'transaction' => $transaction,
    //         'application' => $application,
    //     ])
    //         ->setOptions([
    //             'isRemoteEnabled' => true,
    //             'isHtml5ParserEnabled' => true,
    //         ]);

    //     return $pdf->download('invoice.pdf');
    // }

    // public function generateDownloadLink(string $orderNumber)
    // {
    //     $signedUrl = URL::signedRoute('client.payment.pdf', ['order_number' => $orderNumber]);

    //     return $signedUrl;
    // }

    // public function emailPaymentReceipt(array $data, Application $application)
    // {
    //     $orderNumber = $data['orderNumber'];
    //     $email = $data['email'];

    //     $transaction = Transaction::where('order_number', $orderNumber)->first();

    //     $receiptUrl = $this->generateDownloadLink($transaction->order_number);
    //     Mail::to($email)->send(new TransactionReceipt($transaction, $application, $receiptUrl));

    //     return [
    //         'message' => 'Email send successfully'
    //     ];
    // }
}
