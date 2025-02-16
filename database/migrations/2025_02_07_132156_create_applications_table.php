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

            $table->string('name');
            $table->string('support_email');

            $table->text('app_key');
            $table->text('app_secret');

            $table->text('website_url');
            $table->text('success_redirect_url');
            $table->text('fail_redirect_url');

            $table->string('logo')->nullable();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->uuid('environment_id')->nullable();
            $table->foreign('environment_id')->references('id')->on('environments');

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
