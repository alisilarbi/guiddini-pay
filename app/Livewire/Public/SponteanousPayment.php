<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Application;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HandlesWebExceptions;
use Filament\Notifications\Notification;
use App\Services\Payments\PaymentService;
use App\Services\Payments\ReceiptService;

class SponteanousPayment extends Component
{
    use HandlesWebExceptions;

    public Application $application;
    public $amount;
    public $email;
    public $acceptedTerms;
    public $showTransaction = false;
    public $transaction;
    public $orderNumber;

    protected PaymentService $paymentService;
    protected ReceiptService $receiptService;

    public function __construct()
    {
        $this->paymentService = app(PaymentService::class);
        $this->receiptService = app(ReceiptService::class);
    }

    public function mount($slug, $order_number = null)
    {
        $this->application = Application::where('slug', $slug)->firstOrFail();
        $this->orderNumber = $order_number;

        if ($this->orderNumber) {
            $this->transaction = Transaction::where('order_number', $this->orderNumber)->first();
            $this->showTransaction = !!$this->transaction;
        }

        // try {
        // } catch (\Throwable $e) {
        //     $this->handleWebException($e);
        // }
    }

    public function render()
    {
        return view('livewire.public.sponteanous-payment');
    }

    public function pay()
    {
        $this->validate([
            'amount' => 'required|numeric|min:100',
            'acceptedTerms' => 'accepted',
        ], [
            'amount.required' => 'Le montant est requis.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => 'Le montant minimum est de 100 DZD.',
            'acceptedTerms.accepted' => 'Vous devez accepter les termes et conditions.',
        ]);

        try {
            $data = [
                'amount' => $this->amount,
                'origin' => 'System',
            ];

            $result = $this->paymentService->initiatePayment(
                $data,
                $this->application->app_key
            );

            Notification::make()
                ->title('Paiement initié avec succès')
                ->success()
                ->send();

            return redirect()->to($result['formUrl']);
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }

    public function downloadReceipt()
    {
        try {

            $signedUrl = $this->receiptService->generateDownloadLink($this->orderNumber);
            $this->dispatch('download-receipt', url: $signedUrl);

            Notification::make()
                ->title('Reçu téléchargé avec succès')
                ->success()
                ->send();

            // return $signedUrl;
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }

    public function sendEmail()
    {
        $this->validate([
            'orderNumber' => 'required|string',
            'email' => 'required|email',
        ], [
            'orderNumber.required' => 'Le numéro de commande est requis.',
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'L\'adresse e-mail doit être valide.',
        ]);

        try {
            $data = [
                'orderNumber' => $this->orderNumber,
                'email' => $this->email,
                'x-app-key' => $this->application->app_key,
                'x-secret-key' => $this->application->app_secret,
            ];

            $this->receiptService->emailPaymentReceipt($data, $this->application);

            Notification::make()
                ->title('Email envoyé avec succès')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }

    public function tryAgain()
    {
        try {
            return redirect()->route('certification', [
                'slug' => $this->application->slug,
            ]);
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }
}
