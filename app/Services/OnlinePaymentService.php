<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\Request;

class OnlinePaymentService
{
    public function execute(Request $request)
    {
        $request->validate([
            'pack_name' => 'required',
            'price' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
        ]);

        $transaction = Transaction::create([
            'pack_name' => $request->pack_name,
            'price' => $request->price,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);


        return $transaction;

    }
}
