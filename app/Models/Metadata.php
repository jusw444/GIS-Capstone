<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    protected $table = 'tbl_metadata';
    protected $fillable = [
        'meta_key',
        'meta_value',
        'shapefile_id',
    ];

    public function shapefile() {
    return $this->belongsTo(Shapefile::class);
}
}
