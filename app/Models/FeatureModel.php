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
        'feature_no',
        'default_location_id',
        'visibility',
        'created_by',
        'updated_by',
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
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function defaultLocation()
    {
        return $this->belongsTo(DefaultLocation::class, 'default_location_id');
    }

    public function getLocationAttribute()
{
    if ($this->defaultLocation) {
        return implode(', ', array_filter([
            $this->defaultLocation->district,
            $this->defaultLocation->municity,
            $this->defaultLocation->brgy
        ]));
    }
    return 'N/A';
}

public function scopePublicOnly($query)
{
    return $query->where('visibility', 'public');
}

public function scopePrivateOnly($query)
{
    return $query->where('visibility', 'private');
}

public function scopeVisibleTo($query, $user = null)
{
    if (!$user) {
        return $query->where('visibility', 'public'); // guest
    }

    if (in_array($user->role, ['super_admin', 'admin', 'user'])) {
        return $query; // full access
    }

    return $query->where('visibility', 'public');
}
}

