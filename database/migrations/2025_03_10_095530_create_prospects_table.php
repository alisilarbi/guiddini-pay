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
        Schema::create('prospects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('legal_status')->nullable();
            $table->boolean('has_bank_account')->default(false);
            $table->string('bank_name')->nullable();
            $table->boolean('converted')->default(false);

            $table->boolean('website_integration')->default(false);
            $table->boolean('mobile_integration')->default(false);

            $table->string('needs_help')->nullable();
            $table->string('reference')->nullable();

            $table->text('website_link')->nullable();
            $table->json('programming_languages')->nullable();

            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->uuid('application_id')->nullable();
            $table->foreign('application_id')->references('id')->on('applications');

            $table->uuid('partner_id')->nullable();
            $table->foreign('partner_id')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
