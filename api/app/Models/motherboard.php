<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class motherboard extends Model
{
    use HasFactory;

    protected $table = 'motherboard';
    protected $fillable  = [
        'id	',
        'name',
        'imageUrl',
        'brandId',    'socketTypeId', 'ramMemoryTypeId	', 'ramMemorySlots', 'maxTdp	', 'sataSlots', 'm2Slots',    'pciSlots',
    ];
}
