<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'mst_bank';
    protected $primaryKey = 'id_bank';
    public $timestamps = false;

    protected $fillable = [
        'nama_bank',
    ];
}
