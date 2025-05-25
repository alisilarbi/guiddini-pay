<?php

namespace App\Livewire\Hooks;

use App\Models\User;
use Livewire\Component;
use App\Models\Application;
use App\Traits\HandlesWebExceptions;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Services\InternalPayments\InternalPaymentService;

class ApplicationsAllowanceOverview extends Component
{
    use HandlesWebExceptions;

    public User $partner;
    public $totalApplications;
    public $paidApplications;
    public $unpaidApplications;
    public $remainingAllowance;
    public $newAllowance;
    public $applicationPrice;

    protected InternalPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = app(InternalPaymentService::class);
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
                'amount' => $this->newAllowance * $this->applicationPrice,
                'quantity' => $this->newAllowance,
                'origin' => 'Quota Credit',
                'partner_id' => $this->partner->id,
            ];

            $result = $this->paymentService->initiatePayment(
                $data
            );

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
