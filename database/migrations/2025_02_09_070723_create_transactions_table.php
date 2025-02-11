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

            $table->uuid('application_name');
            $table->foreign('application_name')->references('id')->on('applications');

            $table->string('pack_name');
            $table->double('price', 10, 2);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->nullable();


            $table->string('client_order_id')->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_bool')->nullable();
            $table->string('gateway_response_message')->nullable();
            $table->string('gateway_error_code')->nullable();
            $table->string('gateway_code')->nullable();

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
