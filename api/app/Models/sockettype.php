<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sockettype extends Model
{
    use HasFactory;
    protected $table = 'sockettype';

    protected $fillable  = [
        'id', 'name'
    ];
}
