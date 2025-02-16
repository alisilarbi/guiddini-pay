<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentGatewayEnvironmentController extends Controller
{
    use HasUuids;

    protected $fillable = [
        'user_id',

        'environment_type',

        'satim_development_username',
        'satim_development_password',
        'satim_development_terminal',

        'satim_production_username',
        'satim_production_password',
        'satim_production_terminal',

    ];

    protected $casts = [
        'ip_whitelist' => 'array',
        'api_password' => 'encrypted',
    ];

    public function
}
