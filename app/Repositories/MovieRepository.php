<?php

namespace App\Repositories;

class MovieRepository extends AbstractRepository
{

    public function all() {
        return self::cache(
            \App\Models\Movie::with('images', 'groupVideos.videos', 'directors', 'actors')->get(),
            60
        );
    }
}