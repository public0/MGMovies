<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public $timestamps = false;

    public function actors() {
        return $this->belongsToMany(Actor::class);
    }

    public function directors() {
        return $this->belongsToMany(Director::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function groupVideos()
    {
        return $this->hasMany(GroupVideo::class);
    }

}
