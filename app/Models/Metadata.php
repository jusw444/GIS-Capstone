<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $table = 'tbl_metadata';
    protected $fillable = [
        'feature_id',
        'meta_key',
        'meta_value',

    ];

    public function feature()
    {
        return $this->belongsTo(FeatureModel::class, 'feature_id');
    }

}
