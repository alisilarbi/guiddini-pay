<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;

class CredentialsService
{
    public function getFor(Transaction $transaction, string $type): string
    {
        $env = $transaction->application->environment;
        $prefix = $transaction->environment_type === 'production'
            ? 'satim_production'
            : 'satim_development';

        $value = $env->{$prefix . '_' . $type}
            ?? throw new PaymentException(
                "Missing $type credential",
                'GATEWAY_CONFIG_ERROR',
                500
            );

        return $value;
    }
}