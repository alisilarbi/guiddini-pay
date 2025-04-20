<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Application;
use Illuminate\Http\Request;

class SponteanousPayment extends Component
{
    public Application $application;

    public float $amount;

    public function mount($slug)
    {
        $this->application = Application::where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.public.sponteanous-payment');
    }

    public function submit()
    {

    }
}
