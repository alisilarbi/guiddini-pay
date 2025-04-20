<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        DB::transaction(function () {
            $applications = DB::table('applications')->get();

            foreach ($applications as $application) {
                $baseSlug = Str::slug($application->name);
                $slug = $baseSlug;
                $counter = 1;

                while (DB::table('applications')->where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                DB::table('applications')
                    ->where('id', $application->id)
                    ->update(['slug' => $slug]);
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
