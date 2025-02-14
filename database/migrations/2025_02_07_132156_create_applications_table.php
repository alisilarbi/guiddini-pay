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
        Schema::create('applications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->text('app_key');
            $table->text('app_secret');
            $table->text('website_url');
            $table->text('success_redirect_url');
            $table->text('fail_redirect_url');
            $table->boolean('is_active')->default(true);

            $table->string('satim_development_username');
            $table->string('satim_development_password');
            $table->string('satim_development_terminal');

            $table->string('satim_production_username')->nullable();
            $table->string('satim_production_password')->nullable();
            $table->string('satim_production_terminal')->nullable();

            $table->string('environment')->default('development');

            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
