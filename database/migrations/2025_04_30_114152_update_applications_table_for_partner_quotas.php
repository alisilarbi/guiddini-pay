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
        Schema::table('applications', function (Blueprint $table) {
            $table->uuid('quota_transaction_id')->nullable();
            $table->foreign('quota_transaction_id')->references('id')->on('quota_transactions');

            $table->boolean('is_paid')->default(false);
        });
    }

};
