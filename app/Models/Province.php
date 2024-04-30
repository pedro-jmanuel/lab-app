<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'description'
    ];


    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
