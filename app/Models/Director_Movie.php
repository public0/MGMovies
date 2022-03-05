<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Director_Movie extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = ['id'];

    public function movies() {
        return $this->belongsToMany(Movie::class);
    }
}
