<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classification extends Model
{
    use SoftDeletes;
    protected $fillable = ['category_id', 'name', 'color'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function features()
    {
        return $this->hasMany(FeatureModel::class);
    }
}
