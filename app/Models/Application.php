<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Application extends Model
{

    use HasUuids;

    protected $fillable = [
        'name',
        'username',
        'password',
        'terminal',
        'app_key',
        'secret_key',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
