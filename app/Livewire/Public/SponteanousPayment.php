<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\Payments\PaymentService;
use App\Services\Payments\ReceiptService;

class SponteanousPayment extends Component
{
    public Application $application;

    public $amount;
    public $email;
    public $acceptedTerms;
    public $showTransaction = false;
    public $transaction;
    public $orderNumber;

    public function mount($slug, $order_number = null)
    {
        $this->application = Application::where('slug', $slug)->firstOrFail();
        $this->orderNumber = $order_number;

        if ($this->orderNumber) {
            $this->transaction = Transaction::where('order_number', $this->orderNumber)->first();
        } else {
            $this->transaction = null;
        }
    }

    public function render()
    {
        return view('livewire.public.sponteanous-payment');
    }

    public function pay(PaymentService $paymentService)
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

        $data['amount'] = $this->amount;
        $data['origin'] = 'System';

        $result = $paymentService->initiatePayment(
            $data,
            $this->application->app_key,
        );


        return redirect()->to($result['formUrl']);
    }

    public function tryAgain()
    {
        return redirect()->route('certification', [
            'slug' => $this->application->slug,
        ]);
    }

    public function downloadReceipt(ReceiptService $receiptService)
    {
        $signedUrl = $receiptService->generateDownloadLink($this->transaction->order_number);
        $this->dispatch('download-receipt', url: $signedUrl);
    }

    public function sendEmail(ReceiptService $receiptService)
    {
        $this->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
        ]);

        $data = [
            'orderNumber' => $this->transaction->order_number,
            'email' => $this->email,
        ];

        $receiptService->emailPaymentReceipt($data, $this->application);

        $this->dispatch('close-modal', id: 'send-email');
        // session()->flash('message', 'Email envoyé avec succès.');
    }

}
