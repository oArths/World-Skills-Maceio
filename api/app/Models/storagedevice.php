<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class storagedevice extends Model
{
    use HasFactory;

    protected $table = 'storagedevice';
    protected $fillable  = [
        'id',    'name',    'imageUrl',    'brandId',    'storageDeviceType',    'size', 'storageDeviceInterface'
    ];
}
