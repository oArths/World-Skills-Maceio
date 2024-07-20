<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rammemory extends Model
{
    use HasFactory;

    protected $table = 'rammemory';

    protected $fillable  = [
        'id',
        'name',    'imageUrl',    'brandId',    'size',    'ramMemoryTypeId',    'frequency'
    ];
}
