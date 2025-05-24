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
            $table->uuid('application_id')->nullable()->change();
            $table->uuid('license_id')->nullable()->change();
            $table->string('license_env')->nullable()->change();

            $table->json('quota_transactions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('application_id')->nullable()->change();
            $table->uuid('license_id')->nullable()->change();
            $table->string('license_env')->nullable()->change();
            $table->dropColumn('quota_transactions');
        });
    }
};
