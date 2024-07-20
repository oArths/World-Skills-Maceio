<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class processor extends Model
{
    use HasFactory;

    protected $table = 'processor';
    protected $fillable  = [
        'id',
        'name',
        'imageUrl',
        '	brandId',
        'socketTypeId',
        '	cores',    'baseFrequency',    'maxFrequency',    'cacheMemory',    'tdp'

    ];
}
