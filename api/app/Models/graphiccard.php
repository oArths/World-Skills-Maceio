<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class graphiccard extends Model
{
    use HasFactory;
    protected $table = 'graphiccard';
    protected $fileable = [
        'id',	
        'name',	
        'imageUrl',
        	'brandId',
            'memorySize',	
            'memoryType',
            	'minimumPowerSupply',
                	'supportMultiGpu',
    ];

}
