<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupVideo extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = ['id'];

    public function videos() {
        return $this->hasMany(Video::class);
    }
}
