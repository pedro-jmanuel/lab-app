<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'classrooms',
        'province_id'
    ];
    

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
