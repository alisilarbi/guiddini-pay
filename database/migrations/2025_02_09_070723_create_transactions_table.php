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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('status')->default('pending');
            $table->string('amount', 10, 2)->nullable();
            $table->string('order_number')->nullable();
            $table->string('order_id')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('deposit_amount')->nullable();
            $table->string('currency')->nullable();
            $table->string('auth_code')->nullable();
            $table->string('action_code')->nullable();
            $table->string('action_code_description')->nullable();
            $table->string('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->string('confirmation_status')->nullable();

            $table->string('environment_type');

            $table->uuid('application_id');
            $table->foreign('application_id')->references('id')->on('applications');

            $table->uuid('environment_id');
            $table->foreign('environment_id')->references('id')->on('environments');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
