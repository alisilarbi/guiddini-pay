<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->string('gateway_type')->after('satim_production_terminal')->nullable();

            $table->string('poste_dz_development_username')->after('gateway_type')->nullable();
            $table->string('poste_dz_development_password')->after('poste_dz_development_username')->nullable();
            $table->string('poste_dz_production_username')->after('poste_dz_development_password')->nullable();
            $table->string('poste_dz_production_password')->after('poste_dz_production_username')->nullable();
        });

        DB::table('licenses')->update(['gateway_type' => 'satim']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_type',
                'poste_dz_development_username',
                'poste_dz_development_password',
                'poste_dz_production_username',
                'poste_dz_production_password',
            ]);
        });
    }
};
