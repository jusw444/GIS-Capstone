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
        Schema::table('default_locations', function (Blueprint $table) {
            $table->string('boundary_type')->nullable()->after('brgy');
            $table->index('boundary_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('default_locations', function (Blueprint $table) {
            $table->dropColumn('boundary_type');
        });
    }
};
