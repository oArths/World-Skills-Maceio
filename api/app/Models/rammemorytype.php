<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rammemorytype extends Model
{
    use HasFactory;

    protected $table = 'rammemorytype';

    protected $fileable = [
        'id',	'name'
    ];
}
