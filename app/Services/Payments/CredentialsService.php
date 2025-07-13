<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;

class CredentialsService
{

    /**
     * Fetch a specific credential (username, password, or terminal) for a transaction.
     *
     * @param Transaction $transaction The transaction to fetch credentials for
     * @param string $type The type of credential ('username', 'password', or 'terminal')
     * @return string The requested credential value
     * @throws PaymentException If the credential is missing or invalid
     */

    public function getFor(Transaction $transaction, string $type): string
    {
        $license = $transaction->application->license;
        $gatewayType = $license->gateway_type;
        $env = $transaction->license_env; // 'development' or 'production'

        $prefix = $gatewayType . '_' . $env;

        if ($type === 'terminal' && $gatewayType === 'satim') {
            return $license->{$prefix . '_terminal'} ?? throw new PaymentException(
                "Missing terminal credential for SATIM",
                'GATEWAY_CONFIG_ERROR',
                500
            );
        }

        if (in_array($type, ['username', 'password'])) {
            return $license->{$prefix . '_' . $type} ?? throw new PaymentException(
                "Missing $type credential for $gatewayType",
                'GATEWAY_CONFIG_ERROR',
                500
            );
        }

        throw new PaymentException("Invalid credential type requested: $type", 'INVALID_CREDENTIAL_TYPE', 500);
    }
}
