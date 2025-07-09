<?php

namespace App\Models;

use App\Models\User;
use App\Models\Quota;
use App\Models\Environment;
use Illuminate\Support\Str;
use App\Models\ProductionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Application extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',

        'app_key',
        'app_secret',

        'website_url',
        'redirect_url',

        'logo',
        'user_id',

        'license_id',
        'license_env',

        'partner_id',

        'payment_status',
        'quota_id',
    ];

    public static function generateAppKey(): string
    {
        return 'APP-' . strtoupper(Str::random(18));
    }

    public static function generateSecretKey(): string
    {
        return 'SEC-' . Str::random(32);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function license()
    {
        return $this->belongsTo(License::class, 'license_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'application_id');
    }

    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {

            $baseSlug = Str::slug($application->name);
            $slug = $baseSlug;
            $counter = 1;

            while (DB::table('applications')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $application->slug = $slug;
            $application->app_key = self::generateAppKey();
            $application->app_secret = self::generateSecretKey();

            if (auth()->check()) {
                $application->user_id = Auth::user()->id;
            }
        });

        static::saving(function ($application) {
            // Reload the license relationship if license_id has changed
            if ($application->isDirty('license_id')) {
                $application->load('license');
            }

            // Check if a license is associated
            if ($application->license) {
                $availableEnvs = $application->getAvailableEnvs();
                // If the current license_env isn’t valid for the new license, update it
                if (!in_array($application->license_env, $availableEnvs)) {
                    $application->license_env = $availableEnvs[0] ?? null;
                }
            } else {
                // If no license exists, reset license_env to null
                $application->license_env = null;
            }
        });
    }

    public function getAvailableEnvs()
    {
        $license = $this->license;
        if (!$license) {
            return [];
        }

        $envs = [];

        if ($license->gateway_type === 'satim') {
            // Check SATIM development credentials
            if (
                $license->satim_development_username &&
                $license->satim_development_password &&
                $license->satim_development_terminal
            ) {
                $envs[] = 'development';
            }
            // Check SATIM production credentials
            if (
                $license->satim_production_username &&
                $license->satim_production_password &&
                $license->satim_production_terminal
            ) {
                $envs[] = 'production';
            }
        } elseif ($license->gateway_type === 'poste_dz') {
            // Check PosteDz development credentials
            if (
                $license->poste_dz_development_username &&
                $license->poste_dz_development_password
            ) {
                $envs[] = 'development';
            }
            // Check PosteDz production credentials
            if (
                $license->poste_dz_production_username &&
                $license->poste_dz_production_password
            ) {
                $envs[] = 'production';
            }
        }

        return $envs;
    }

    public function quota()
    {
        return $this->belongsTo(Quota::class, 'quota_id');
    }

    public function eventHistories()
    {
        return $this->morphMany(EventHistory::class, 'eventable');
    }
}
