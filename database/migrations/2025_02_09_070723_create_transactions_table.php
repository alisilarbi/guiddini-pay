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
            $table->string('amount', 10, 2);

            $table->string('client_order_id')->nullable();

            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_confirmation_status')->nullable();
            $table->string('gateway_response_message')->nullable();
            $table->string('gateway_error_code')->nullable();
            $table->string('gateway_code')->nullable();

            $table->uuid('application_id');
            $table->foreign('application_id')->references('id')->on('applications');

            $table->timestamp('paid_at')->nullable();

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
