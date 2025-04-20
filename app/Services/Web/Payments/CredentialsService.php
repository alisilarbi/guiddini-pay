<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;

class CredentialsService
{
    public function getFor(Transaction $transaction, string $type): string
    {
        $license = $transaction->application->license;
        $prefix = $transaction->license_env === 'production'
            ? 'satim_production'
            : 'satim_development';

        $value = $license->{$prefix . '_' . $type}
            ?? throw new PaymentException(
                "Missing $type credential",
                'GATEWAY_CONFIG_ERROR',
                500
            );

        return $value;
    }
}