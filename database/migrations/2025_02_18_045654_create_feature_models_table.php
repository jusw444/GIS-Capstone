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
        Schema::create('feature_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shapefile_id')
                ->cascadeOnDelete()
                ->constrained('tbl_shapefiles');
            $table->geometry('geometry');
            $table->integer('feature_no');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_models');
    }
};
