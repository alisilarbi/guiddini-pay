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

            $table->string('pack_name');
            $table->double('price', 10, 2);
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('status');


            $table->string('client_order_id');
            $table->string('gateway_order_id');
            $table->string('gateway_bool');
            $table->string('gateway_response_message');
            $table->string('gateway_error_code');
            $table->string('gateway_code');

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
