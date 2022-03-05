<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actor_Movie extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = ['id'];

    public function movies() {
        return $this->belongsToMany(Movie::class);
    }
}
