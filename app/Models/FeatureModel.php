<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FeatureModel extends Model
{
    protected $table = 'feature_models';
    protected $fillable = [
        'shapefile_id',
        'geometry',
        'feature_no',
    ];

    public function shapefile()
    {
        return $this->belongsTo(Shapefile::class, 'shapefile_id');
    }

    public function metadata()
    {
        return $this->hasMany(Metadata::class, 'feature_id');
    }

    
}
