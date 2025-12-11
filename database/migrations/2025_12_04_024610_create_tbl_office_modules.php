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
        Schema::create('tbl_office_modules', function (Blueprint $table) {
            $table->id();
            $table->string('file');
            $table->enum('category', ['disaster', 'health', 'land_use'])->index();

            $table->foreignId('shapefile_id')
                ->nullable()
                ->constrained('tbl_shapefiles')
                ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_office_modules');
    }
};
