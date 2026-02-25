<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function shapefiles()
    {
        return $this->hasMany(Shapefile::class);
    }

    public function officeModules()
    {
        return $this->hasMany(OfficeModule::class);
    }
}
