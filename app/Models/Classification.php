<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $fillable = ['category_id', 'name', 'color'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shapefiles()
    {
        return $this->hasMany(Shapefile::class);
    }
}
