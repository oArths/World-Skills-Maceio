<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class processor extends Model
{
    use HasFactory;

    protected $table = 'processor';
    protected $fileable = [
        'id',	
        'name',	
        'imageUrl',
        '	brandId',	
        'socketTypeId',
        '	cores',	'baseFrequency',	'maxFrequency',	'cacheMemory',	'tdp'

    ];
}
