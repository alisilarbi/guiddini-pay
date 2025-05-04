<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_type',
        'event_code',
        'event_summary',

        'eventable_id',
        'eventable_type',

        'action',
        'payment_status',
        'price',
        'quantity',
        'total',
        'details',

        'user_id',
        'partner_id',
    ];

    protected $casts = [
        'details' => 'array',
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'total' => 'decimal:2',
        'impact' => 'integer',
        'created_at' => 'datetime',
    ];

    public function eventable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'eventable_id');
    }
}
