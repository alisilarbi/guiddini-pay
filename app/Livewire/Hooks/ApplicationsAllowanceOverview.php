<?php

namespace App\Livewire\Hooks;

use App\Models\User;
use Livewire\Component;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Services\InternalPayments\InternalPaymentService;

class ApplicationsAllowanceOverview extends Component
{

    public User $partner;
    public $totalApplications;
    public $paidApplications;
    public $unpaidApplications;
    public $remainingAllowance;
    public $newAllowance;
    public $applicationPrice;
    public $paymentService;

    public function __construct()
    {
        $this->paymentService = app(InternalPaymentService::class);
        // $this->receiptService = app(ReceiptService::class);
    }


    public function mount()
    {
        $this->partner = Auth::user();
        $this->applicationPrice = $this->partner->application_price;

        // Calculate application stats
        $this->totalApplications = $this->partner->total_apps;
        $this->paidApplications = $this->partner->total_paid;
        $this->unpaidApplications =  $this->partner->total_unpaid;
        $this->remainingAllowance = $this->partner->available_quota;
        $this->newAllowance = 1;
    }

    public function checkIfInteger()
    {
        if (!is_int($this->newAllowance)) {
            $this->newAllowance = 1;
        }
    }

    public function upNewAllowance()
    {
        $this->checkIfInteger();
        $this->newAllowance++;
    }

    public function downNewAllowance()
    {
        $this->checkIfInteger();

        if ($this->newAllowance > 1) {
            $this->newAllowance--;
        } else {
            $this->newAllowance = 1;
        }
    }

    public function buyAllowance()
    {
        try {
            $data = [
                'amount' => $this->amount,
                'origin' => 'System',
            ];

            $result = $this->paymentService->initiatePayment(
                $data,
                $this->application->app_key
            );

            dd($result);

            Notification::make()
                ->title('Paiement initié avec succès')
                ->success()
                ->send();

            return redirect()->to($result['formUrl']);
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }


    public function render()
    {
        return view('livewire.hooks.applications-allowance-overview');
    }
}
