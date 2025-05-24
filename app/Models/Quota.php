<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Quota extends Model
{
    use HasUuids;

    protected $fillable = [
        'partner_id',
        'type',
        'payment_status',

        'application_price',
        'quantity',
        'total',

        'remaining_quantity',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'quota_id');
    }
}
