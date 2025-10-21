<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'categories';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'name',
       
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

}
