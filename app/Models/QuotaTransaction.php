<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class QuotaTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'partner_id',
        'type',
        'is_paid',

        'application_price',
        'total',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'quota_transaction_id');
    }
}
