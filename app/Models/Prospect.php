<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Prospect extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'company_name',
        'phone',
        'email',
        'legal_status',
        'has_bank_account',
        'bank_name',
        'website_integration',
        'mobile_integration',
        'website_link',
        'programming_languages',
        'user_id',
    ];

    protected $casts = [
        'programming_languages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}
