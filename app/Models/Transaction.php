<?php

namespace App\Models;

use App\Models\Quota;
use App\Models\Application;
use App\Models\Environment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'origin',
        'status',

        'application_id',
        'environment_id',
        'partner_id',

        'amount',

        'order_number',
        'order_id',
        'card_holder_name',
        'deposit_amount',
        'currency',
        'auth_code',
        'params',
        'action_code',
        'action_code_description',
        'order_status_description',
        'error_code',
        'error_message',
        'order_number',
        'confirmation_status',
        'description',

        'license_env',
        'license_id',

        'form_url',
        'svfe_response',
        'pan',
        'ip_address',
        'approval_code',
        'expiration',
        'quota_transactions',
        'quota_quantity',

        'transaction_status',
        'transaction_status_message',
    ];

    protected $casts = [
        'quota_transactions' => 'array',
        'params' => 'array',
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

    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    public function quotas()
    {
        return $this->hasMany(Quota::class);
    }
}