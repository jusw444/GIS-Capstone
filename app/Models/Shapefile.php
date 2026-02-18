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
        'id',
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

    public function features()
    {
        return $this->hasMany(FeatureModel::class, 'shapefile_id');
    }


    public function officmodule()
    {
        return $this->hasMany(OfficeModule::class);
    }

    public function metadata()
    {
        return $this->hasManyThrough(
            Metadata::class,
            FeatureModel::class,
            'shapefile_id', // Foreign key on FeatureModel
            'feature_id',   // Foreign key on Metadata
            'id',           // Local key on Shapefile
            'id'            // Local key on FeatureModel
        );
    }


    public function getGeometryAttribute()
{
    $geo = DB::selectOne(
        "SELECT ST_AsGeoJSON(geometry) AS geojson FROM tbl_shapefiles WHERE id = ?",
        [$this->id]
    );

    if (!$geo || !$geo->geojson) return null;

    $geoArray = json_decode($geo->geojson, true);

    // If GeometryCollection, wrap as FeatureCollection
    if (isset($geoArray['type']) && $geoArray['type'] === 'GeometryCollection') {
        if (!empty($geoArray['geometries'])) {
            return [
                'type' => 'FeatureCollection',
                'features' => array_map(fn($g) => ['type' => 'Feature', 'geometry' => $g, 'properties' => []], $geoArray['geometries'])
            ];
        }
        return null;
    }

    // If single Polygon or MultiPolygon, wrap as FeatureCollection
    if (isset($geoArray['type']) && in_array($geoArray['type'], ['Polygon', 'MultiPolygon'])) {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                ['type' => 'Feature', 'geometry' => $geoArray, 'properties' => []]
            ]
        ];
    }

    // If already FeatureCollection, return as-is
    if (isset($geoArray['type']) && $geoArray['type'] === 'FeatureCollection') {
        return $geoArray;
    }

    return null;
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
