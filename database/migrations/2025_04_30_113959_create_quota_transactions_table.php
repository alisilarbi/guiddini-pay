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
        Schema::create('quota_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('partner_id');
            $table->foreign('partner_id')->references('id')->on('users');

            $table->string('type');
            $table->boolean('is_paid');

            $table->decimal('application_price', 8, 2)->nullable()->default(null);
            $table->integer('quantity')->default(0);
            $table->decimal('total', 10, 2)->nullable()->default(null);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quota_transactions');
    }
};
