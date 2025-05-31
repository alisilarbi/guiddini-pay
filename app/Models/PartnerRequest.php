<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PartnerRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'business_type',
        'company_name',
        'converted',
        'partner_id',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id', 'id');
    }


}
