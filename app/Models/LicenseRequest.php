<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LicenseRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'application_id',
        'bank_document',
        'registration_document',
        'partner_id',
        'status',
    ];

    public function license()
    {
        return $this->belongsTo(License::class, 'license_id');
    }

}
