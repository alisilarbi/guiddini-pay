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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('order_status_description')->nullable()->after('action_code_description');
            $table->string('expiration')->nullable()->after('approval_code');

            $table->string('transaction_status')->nullable()->after('status');
            $table->string('transaction_status_message')->nullable()->after('status');
        });
    }
};
