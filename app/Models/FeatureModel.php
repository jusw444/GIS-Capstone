<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class FeatureModel extends Model
{
    use SoftDeletes;
    protected $table = 'feature_models';
    protected $fillable = [
        'shapefile_id',
        'classification_id',
        'geometry',
        'survey_date',
        'description',
        'location',
        'feature_no',
    ];
    public function classification()
    {
        return $this->belongsTo(Classification::class);
    }

    public function shapefile()
    {
        return $this->belongsTo(Shapefile::class, 'shapefile_id');
    }

    public function metadata()
    {
        return $this->hasMany(Metadata::class, 'feature_id');
    }

    
}
