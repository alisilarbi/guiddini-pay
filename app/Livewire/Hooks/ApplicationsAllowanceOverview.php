<?php

namespace App\Livewire\Hooks;

use App\Models\User;
use Livewire\Component;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;

class ApplicationsAllowanceOverview extends Component
{

    public User $partner;
    public $totalApplications;
    public $paidApplications;
    public $unpaidApplications;
    public $remainingAllowance;
    public $newAllowance;
    public $applicationPrice;
    public function mount()
    {
        $this->partner = Auth::user();
        $this->applicationPrice = $this->partner->application_price;

        // Calculate application stats
        $this->totalApplications = Application::where('user_id', $this->partner->id)->count();
        $this->paidApplications = Application::where('user_id', $this->partner->id)
            ->where('is_paid', true)
            ->count();
        $this->unpaidApplications = $this->totalApplications - $this->paidApplications;
        $this->remainingAllowance = $this->partner->remaining_allowance;
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
        dd($this->newAllowance);
    }


    public function render()
    {
        return view('livewire.hooks.applications-allowance-overview');
    }
}
