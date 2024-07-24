<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class machinehasstoragedevice extends Model
{
    use HasFactory;
    protected $table = 'machinehasstoragedevice';
    public $timestamps = false;

    protected $fillable  = [
        'machineId', 'storageDeviceId', 'amount',
    ];
}
