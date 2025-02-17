<?php

namespace App\Models;

use App\Models\User;
use App\Models\Environment;
use Illuminate\Support\Str;
use App\Models\ProductionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Application extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',

        'app_key',
        'app_secret',

        'website_url',
        'success_redirect_url',
        'fail_redirect_url',

        'logo',
        'user_id',

        'environment_id',
        'environment_type',
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
        return $this->belongsTo(User::class);
    }

    public function environment()
    {
        return $this->belongsTo(Environment::class, 'id', 'environment_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function($application){
            $application->app_key = self::generateAppKey();
            $application->app_secret = self::generateSecretKey();
            $application->user_id = Auth::user()->id;
        });

        // static::deleting(function ($application) {
        //     $application->info()->delete();
        // });
    }

    // public function __get($key)
    // {
    //     if (array_key_exists($key, $this->attributes))
    //         return parent::__get($key);

    //     if ($this->relationLoaded('info') || $this->info()->exists()) {
    //         return $this->info->{$key} ?? parent::__get($key);
    //     }

    //     return parent::__get($key);
    // }

    // public function __set($key, $value)
    // {
    //     if (array_key_exists($key, $this->attributes)) {
    //         parent::__set($key, $value);
    //         return;
    //     }

    //     if ($this->info) {
    //         $this->info->{$key} = $value;
    //         return;
    //     }

    //     parent::__set($key, $value);
    // }

    // public function update(array $attributes = [], array $options = [])
    // {
    //     $appAttributes = [];
    //     $infoAttributes = [];

    //     foreach ($attributes as $key => $value) {
    //         if (array_key_exists($key, $this->attributes)) {
    //             $appAttributes[$key] = $value;
    //         } else {
    //             $infoAttributes[$key] = $value;
    //         }
    //     }

    //     if (!empty($appAttributes)) {
    //         parent::update($appAttributes, $options);
    //     }

    //     if (!empty($infoAttributes) && $this->info) {
    //         $this->info->update($infoAttributes);
    //     }

    //     return true;
    // }



    // public static function createWithInfo(array $data)
    // {
    //     $data['user_id'] = $data['user_id'] ?? Auth::id();

    //     // $data['satim_development_username'] = 'SAT2301170552';
    //     // $data['satim_development_password'] = 'satim120';
    //     // $data['satim_development_terminal'] = 'E010900790';

    //     $data['app_key'] = self::generateAppKey();
    //     $data['app_secret'] = self::generateSecretKey();

    //     $appColumns = Schema::getColumnListing((new self)->getTable());

    //     $appData = array_intersect_key($data, array_flip($appColumns));
    //     $infoData = array_diff_key($data, $appData);

    //     $application = self::create($appData);

    //     // if (!empty($infoData)) {
    //     //     $infoData['application_id'] = $application->id;
    //     //     // $application->info()->create($infoData);
    //     //     $applicationWithInfo = $application->setRelation('info', $application->info()->create($infoData));
    //     // }

    //     return $applicationWithInfo;
    // }



}
