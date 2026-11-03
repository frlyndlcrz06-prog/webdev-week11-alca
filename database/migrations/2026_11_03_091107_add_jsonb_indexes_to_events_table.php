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
        Schema::table('events', function (Blueprint $table) {
            DB::statement("CREATE INDEX events_details_dress_code_idx ON events ((details->>'dress_code'))");
            DB::statement("CREATE INDEX events_details_gin_idx ON events USING GIN (details jsonb_path_ops)");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            DB::statement('DROP INDEX IF EXISTS events_details_dress_code_idx');
            DB::statement('DROP INDEX IF EXISTS events_details_gin_idx');
        });
    }
};
