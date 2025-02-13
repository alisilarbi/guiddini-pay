<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductionRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'application_id',
        'user_id',
        'status',
        'comments',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($productionRequest) {
            $productionRequest->status = 'Pending';
            $productionRequest->user_id = Auth::user()->id;
        });
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
