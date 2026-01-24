<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeModule extends Model
{
    protected $table = 'tbl_office_modules';
    use SoftDeletes;

    protected $fillable = [
        'file',
        'category',
        'shapefile_id',
    ];

    public function shapefile()
    {
        return $this->belongsTo(Shapefile::class);
    }

    public function modules()
    {
        return $this->belongsTo(User::class);
    }
}
