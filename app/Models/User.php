<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Models\License;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'created_at',
        'password',
        'is_admin',
        'is_partner',
        'is_user',
        'app_key',
        'app_secret',
        'created_by',
        'reset_password_flag',
        'partner_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    // public function canAccessPanel(Panel $panel): bool
    // {
    //     return true;
    // }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => (bool) $this->is_admin,
            'partner' => (bool) $this->is_partner,
            'user' => (bool) $this->is_user,
            default => false,
        };
    }

    public function partner()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateAppKey(): string
    {
        return 'APP-' . strtoupper(Str::random(18));
    }

    public static function generateSecretKey(): string
    {
        return 'SEC-' . Str::random(32);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->app_key = self::generateAppKey();
            $user->app_secret = self::generateSecretKey();
        });

        // static::deleting(function ($application) {
        //     $application->info()->delete();
        // });
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }
}
