<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\License;
use App\Models\Application;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@guiddini.com',
            'password' => 'password',
            'is_admin' => true,
            'is_partner' => false,
            'is_user' => false,
        ]);

        $partner = User::factory()->create([
            'name' => 'Guiddini',
            'email' => 'mourad@guiddini.com',
            'password' => 'password',
            'is_admin' => false,
            'is_partner' => true,
            'is_user' => false,
        ]);

        $license = License::create([
            'name' => 'Guiddini - License',

            'satim_development_username' => 'SAT2301170552',
            'satim_development_password' => 'satim120',
            'satim_development_terminal' => 'E010900790',

            'satim_production_username' => 'guiddini9q41q',
            'satim_production_password' => 'tsgr84sd$rfs',
            'satim_production_terminal' => 'E005000003',

            'user_id' => $partner->id,
            'partner_id' => $partner->id,
        ]);

        $application = Application::create([
            'name' => 'Efawtara',
            'website_url' => 'https://app.efawtara.com',
            'redirect_url' => 'https://app.efawtara.com',
            'user_id' => $partner->id,

            'license_id' => $license->id,
            'license_env' => 'development',
            'partner_id' => $partner->id,
        ]);
    }
}
