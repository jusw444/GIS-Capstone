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
        Schema::create('tbl_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('meta_key')->index();
            $table->text('meta_value')->nullable();

            $table->foreignId('shapefile_id')
                ->nullable()
                ->constrained('tbl_shapefiles')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_metadata');
    }
};
