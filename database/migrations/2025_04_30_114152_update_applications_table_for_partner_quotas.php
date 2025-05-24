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
            $table->uuid('quota_id')->nullable();
            $table->foreign('quota_id')->references('id')->on('quotas');

            $table->string('payment_status')->nullable();
        });
    }


    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign('quota_id');
            $table->dropColumn('quota_id');
            $table->dropColumn('payment_status');

        });
    }
};
