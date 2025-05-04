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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('application_price', 8, 2)->nullable()->default(null)->after('partner_secret');
            $table->string('partner_mode')->default('quota');

            $table->boolean('default_is_paid')->nullable()->default(false);

            $table->unsignedBigInteger('total_apps')->default(0);

            $table->unsignedBigInteger('total_unpaid')->default(0);
            $table->unsignedBigInteger('total_paid')->default(0);

            $table->unsignedBigInteger('used_quota')->default(0);
            $table->unsignedBigInteger('available_quota')->default(0);
        });
    }


    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'application_price',
                'partner_mode',

                'default_is_paid',

                'total_apps',

                'total_paid',
                'total_unpaid',

                'used_quota',
                'available_quota',
            ]);
        });
    }
};
