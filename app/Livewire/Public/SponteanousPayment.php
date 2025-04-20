<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Services\Payments\PaymentService;

class SponteanousPayment extends Component
{
    public Application $application;

    public $amount;

    protected $rules = [
        'amount' => 'required',
    ];

    protected $messages = [
        'amount.required' => 'Le montant est obligatoire.',
    ];

    public function mount($slug)
    {
        $this->application = Application::where('slug', $slug)->firstOrFail();
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
