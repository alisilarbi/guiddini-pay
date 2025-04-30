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
        Schema::table('Users', function (Blueprint $table) {
            $table->decimal('application_price', 8, 2)->nullable()->default(null);
            $table->string('partner_mode')->default('quota');
            $table->integer('remaining_allowance')->nullable()->default(0);
            $table->boolean('default_is_paid')->nullable()->default(false);
        });
    }

};
