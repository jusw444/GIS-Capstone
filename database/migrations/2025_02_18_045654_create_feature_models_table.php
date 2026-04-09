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
            $table->foreignId('classification_id')->nullable()->constrained('classifications')->nullOnDelete();
            $table->foreignId('shapefile_id')
                ->constrained('tbl_shapefiles')
                ->cascadeOnDelete();
            $table->foreignId('default_location_id')->nullable()->constrained('default_locations')->nullOnDelete();
            $table->geometry('geometry');
            $table->date('survey_date')->nullable();
            $table->text('description');
            $table->integer('feature_no');
            $table->softDeletes();
            $table->timestamps();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');  
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
