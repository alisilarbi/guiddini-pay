<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Environment extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'name',

        'satim_development_username',
        'satim_development_password',
        'satim_development_terminal',

        'satim_production_username',
        'satim_production_password',
        'satim_production_terminal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
