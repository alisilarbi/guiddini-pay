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
        Schema::create('application_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('application_id');
            $table->foreign('application_id')->references('id')->on('applications');

            $table->string('name');
            $table->string('support_email');
            $table->json('industries')->nullable();
            $table->string('logo')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->string('terms_of_service_url')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_infos');
    }
};
