<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultLocation extends Model
{
    protected $fillable =[
        'geometry',
        'district',
        'municity',
        'brgy',
    ];
}
