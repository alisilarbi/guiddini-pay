<?php

namespace App\Models;

use App\Models\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'application_id',

        'amount',

        'order_number',
        'order_id',
        'bool',
        'response_message',
        'confirmation_status',
        'error_code',
        'code',
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
