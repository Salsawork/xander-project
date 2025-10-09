<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opinion extends Model
{
    protected $fillable = [
        'email',
        'subject',
        'description',
    ];
}
