<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');

            $table->string('satim_development_username')->nullable();
            $table->string('satim_development_password')->nullable();
            $table->string('satim_development_terminal')->nullable();

            $table->string('satim_production_username')->nullable();
            $table->string('satim_production_password')->nullable();
            $table->string('satim_production_terminal')->nullable();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->uuid('partner_id');
            $table->foreign('partner_id')->references('id')->on('users');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
