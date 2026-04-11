<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('default_locations', function (Blueprint $table) {
            $table->id();
            $table->geometry('geometry');
            $table->string('municity')->nullable();
            $table->string('district')->nullable();
            $table->string('brgy')->nullable();
            $table->timestamps();
            
            // Add spatial index for better performance
            $table->spatialIndex('geometry');
        });
        
        // Add indexes for filter columns
        Schema::table('default_locations', function (Blueprint $table) {
            $table->index('district');
            $table->index('municity');
            $table->index('brgy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('default_locations');
    }
};