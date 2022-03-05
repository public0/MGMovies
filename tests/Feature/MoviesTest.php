<?php

namespace Tests\Feature;

use App\Services\Importer\ImporterFacade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoviesTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @dataProvider jsonInputCase
     * @param string $json
     * @return void
     */
    public function test_import (string $json)
    {
        ImporterFacade::importMovies($json, 30, false);

        $this->assertDatabaseCount('movies', 2);
        $this->assertDatabaseCount('images', 20);
        $this->assertDatabaseCount('videos', 6);
        $this->assertDatabaseCount('group_videos', 2);
        $this->assertDatabaseCount('actors', 9);
    }

    public function jsonInputCase():array {
        $movies = file_get_contents(
            __DIR__.DIRECTORY_SEPARATOR.'Data'.DIRECTORY_SEPARATOR.'movies.json',
            FILE_USE_INCLUDE_PATH);
        return [
            [$movies]
        ];
    }
}
