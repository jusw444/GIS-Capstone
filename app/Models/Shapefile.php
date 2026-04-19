<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Shapefile extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_shapefiles';

    protected $fillable = [
        'category_id',
        'user_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
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

    // -------------------------------
    // Accessor: get GeoJSON formatted
    // -------------------------------
//     public function getGeometryAttribute()
// {
//     $features = $this->features()->get(); // load all features

//     if ($features->isEmpty()) {
//         return [
//             'type' => 'FeatureCollection',
//             'features' => [],
//         ];
//     }

//     $geoFeatures = $features->map(function ($f) {
//         // Convert geometry to GeoJSON directly via DB
//         $geo = DB::selectOne(
//             "SELECT ST_AsGeoJSON(geometry) as geojson FROM feature_models WHERE id = ?",
//             [$f->id]
//         );

//         return [
//             'type' => 'Feature',
//             'geometry' => json_decode($geo->geojson, true),
//             'properties' => [],
//         ];
//     })->toArray();

//     return [
//         'type' => 'FeatureCollection',
//         'features' => $geoFeatures,
//     ];
// }

// -------------------------------
// Mutator: update geometry using raw SQL
// -------------------------------
    /**
     * Update all features for this shapefile
     * $geoJson must be a FeatureCollection or a single Polygon/MultiPolygon
     */
    public function setGeometryRaw(array|object $geoJson)
    {
        // Convert single Polygon / MultiPolygon to FeatureCollection
        if (isset($geoJson->type) && in_array($geoJson->type, ['Polygon', 'MultiPolygon'])) {
            $geoJson = [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => $geoJson,
                        'properties' => []
                    ]
                ]
            ];
        }

        if (is_array($geoJson) && !isset($geoJson['type'])) {
            throw new \Exception("Invalid GeoJSON format.");
        }

        if ($geoJson['type'] !== 'FeatureCollection' || empty($geoJson['features'])) {
            throw new \Exception("GeoJSON must be a FeatureCollection with features.");
        }

        // Delete old features
        $this->features()->delete();

        // Insert new features
        foreach ($geoJson['features'] as $index => $feature) {
            if (!isset($feature['geometry'])) continue;

            $this->features()->create([
                'geometry' => DB::raw("ST_GeomFromGeoJSON('" . addslashes(json_encode($feature['geometry'])) . "')"),
                'feature_no' => $index,
            ]);
        }
    }
}
