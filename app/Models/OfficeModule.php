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
        'category_id',
        'user_id',
        'shapefile_id',
        'visibility',
    ];

    public function shapefile()
    {
        return $this->belongsTo(Shapefile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
