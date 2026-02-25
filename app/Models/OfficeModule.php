<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeModule extends Model
{
    protected $table = 'tbl_office_modules';
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'category',
        
        
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
