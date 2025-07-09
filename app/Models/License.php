<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class License extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'partner_id',
        'name',

        'gateway_type',

        'satim_development_username',
        'satim_development_password',
        'satim_development_terminal',

        'satim_production_username',
        'satim_production_password',
        'satim_production_terminal',

        'poste_dz_development_username',
        'poste_dz_development_password',
        'poste_dz_production_username',
        'poste_dz_production_password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function successfulTransactions()
    {
        return $this->hasMany(Transaction::class)->where('status', 'paid');
    }
}
