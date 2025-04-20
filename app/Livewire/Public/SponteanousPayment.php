<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Services\Payments\PaymentService;

class SponteanousPayment extends Component
{
    public Application $application;

    public $amount;
    public $showTransaction = false;
    public $transaction;
    public $orderNumber;

    protected $rules = [
        'amount' => 'required',
    ];

    protected $messages = [
        'amount.required' => 'Le montant est obligatoire.',
    ];

    public function mount($slug, $order_number = null)
    {
        $this->application = Application::where('slug', $slug)->firstOrFail();
        $this->orderNumber = $order_number;

        // Check if orderNumber is not null before querying the transaction
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

    public function submit(PaymentService $paymentService)
    {
        $this->validate();

        $data['amount'] = $this->amount;
        $data['origin'] = 'System';

        $result = $paymentService->initiatePayment(
            $data,
            $this->application->app_key,
        );

        return redirect()->to($result['formUrl']);
    }
}
