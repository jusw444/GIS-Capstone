<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Shapefile extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_shapefiles';

    protected $fillable = [
        'geometry',
        'category',
        'user_id',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function metadata()
    {
        return $this->hasMany(Metadata::class, 'shapefile_id');
    }

    public function officmodule()
    {
        return $this->hasMany(OfficeModule::class);
    }

    public function getGeometryAttribute()
    {
        $geo = DB::selectOne(
            "SELECT ST_AsGeoJSON(geometry) AS geojson FROM tbl_shapefiles WHERE id = ?",
            [$this->id]
        );

        return $geo && $geo->geojson ? json_decode($geo->geojson, true) : null;
    }

    /**
     * Update geometry using raw SQL
     */
    public function setGeometryRaw(string $geoJson)
    {
        DB::table($this->table)
            ->where('id', $this->id)
            ->update(['geometry' => DB::raw("ST_GeomFromGeoJSON('".addslashes($geoJson)."')")]);
    }
}
