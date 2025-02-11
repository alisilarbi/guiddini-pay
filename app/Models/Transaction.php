<?php

namespace App\Models;

use App\Models\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'pack_name',
        'price',
        'name',
        'email',
        'phone',
        'status',
        'application_id',

        'client_order_id',
        'gateway_order_id',
        'gateway_bool',
        'gateway_response_message',
        'gateway_confirmation_status',
        'gateway_error_code',
        'gateway_code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->status = 'Processing';
        });
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
