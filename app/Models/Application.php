<?php

namespace App\Models;

use App\Models\User;
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
    }
}
