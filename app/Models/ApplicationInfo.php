<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApplicationInfo extends Model
{
    use HasUuids;

    protected $fillable = [
        'application_id',
        'name',
        'support_email',
        'industries',
        'logo',
        'privacy_policy_url',
        'terms_of_service_url',
    ];

    protected $casts = [
        'industries' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }


}
