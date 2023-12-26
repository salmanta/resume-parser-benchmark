<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParserResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'resume_id',
        'name',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
