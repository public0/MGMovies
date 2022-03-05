<?php


namespace App\Services\Importer;

use App\Models\Actor;
use App\Models\Director;
use App\Models\GroupVideo;
use App\Models\Movie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Output\ConsoleOutput;

class Importer
{
    private ConsoleOutput $output;

    public function __construct(
        ConsoleOutput $output
    )
    {
        $this->output = $output;
    }

    public function importMovies(string $json, int $batchSize = 30, bool $trace = false) {
        $movies = iconv('UTF-8', 'UTF-8//TRANSLIT', utf8_encode($json));
        $movies = LazyCollection::make(json_decode($movies));
        $moviesCount = $movies->count();
        $moviesArray = [];
        $imagesArray = [];
        $actorsArray = [];
        $videoArray  = [];
        $directorArray = [];

        $counter = 0;
        foreach ($movies as $movie) {
            $moviesArray[] = [
                'UUID' => isset($movie->id)?$movie->id:'',
                'body' => isset($movie->body)?$movie->body:'',
                'cert' => isset($movie->body)?$movie->cert:'',
                'duration' => isset($movie->duration)?$movie->duration:0,
                'headline' => isset($movie->headline)?$movie->headline:'',
                'quote' => isset($movie->quote)?$movie->quote:'',
                'rating' => isset($movie->rating)?$movie->rating:0,
                'reviewAuthor' => isset($movie->reviewAuthor)?$movie->reviewAuthor:'',
                'sky_go_id' => isset($movie->skyGoId)?$movie->skyGoId:'',
                'sky_go_url' => isset($movie->skyGoUrl)?$movie->skyGoUrl:'',
                'sum' => isset($movie->sum)?$movie->sum:'',
                'synopsis' => isset($movie->synopsis)?$movie->synopsis:'',
                'url' => isset($movie->url)?$movie->url:'',
                'vw_start_date' => isset($movie->viewingWindow->startDate)?$movie->viewingWindow->startDate:NULL,
                'vw_wtw' => isset($movie->viewingWindow->wayToWatch)?$movie->viewingWindow->wayToWatch:'',
                'vw_end_date' => isset($movie->viewingWindow->endDate)?$movie->viewingWindow->endDate:NULL,
                'year' => isset($movie->year)?$movie->year:'',
            ];
            foreach ($movie->cardImages as $image) {
                $imagesArray[$movie->id][] = [
                    'url' => $image->url,
                    'local_path' => '',
                    'h' => $image->h,
                    'w' => $image->w,
                    'type' => 1
                ];
            }
            foreach ($movie->keyArtImages as $image) {
                $imagesArray[$movie->id][] = [
                    'url' => $image->url,
                    'local_path' => '',
                    'h' => $image->h,
                    'w' => $image->w,
                    'type' => 2
                ];
            }

            foreach ($movie->cast as $actor) {
                $actorsArray[$movie->id][] = [
                    'name' => $actor->name
                ];
            }

            foreach ($movie->directors as $director) {
                $directorArray[$movie->id][] = [
                    'name' => $director->name
                ];
            }

            if(isset($movie->videos)) {
                foreach ($movie->videos as $video) {
                    $videoArray[$movie->id][] = $video;
                }
            }

            ++$counter;
            if(($counter % $batchSize) == 0 || $counter == $moviesCount) {
                $movieBatchCounter = count($moviesArray);

                if($trace)
                    $this->output->writeln("<question>Adding {$movieBatchCounter} movies and dependencies!</question>");
                Movie::insert($moviesArray);
                $moviesBatch = Movie::orderBy('id', 'desc')->take($movieBatchCounter)->get();
                foreach ($moviesBatch as $movieRow) {
                    if($trace) {
                        $this->output->writeln("<comment>Movie {$movieRow->UUID}</comment>");
                        $this->output->writeln("<info>Adding ".count($imagesArray[$movieRow->UUID])." images!</info>");
                    }
                    foreach ($imagesArray[$movieRow->UUID] as &$i) {
                        try {
                            $url = $i['url'];
                            $arrContextOptions=array(
                                "ssl"=>array(
                                    "verify_peer"=>false,
                                    "verify_peer_name"=>false,
                                ),
                            );
                            $contents = file_get_contents($url, false, stream_context_create($arrContextOptions));
                            $name = substr($url, strrpos($url, '/') + 1);

                            $i['local_path'] = 'public/images/'."{$movieRow->UUID}/".$name;

                            Storage::put($i['local_path'], $contents);

                        } catch(\Exception $e) {
                            $i['local_path'] = NULL;
                        }
                    }
                    $movieRow->images()->createMany($imagesArray[$movieRow->UUID]);

                    if($trace)
                        $this->output->writeln("<info>Adding ".count($actorsArray[$movieRow->UUID])." actors!</info>");
                    foreach ($actorsArray[$movieRow->UUID] as $actor) {
                        $actorRow = Actor::firstOrCreate(['name' => $actor['name']]);
                        $movieRow->actors()->attach($actorRow);
                    }

                    if($trace)
                        $this->output->writeln("<info>Adding ".count($directorArray[$movieRow->UUID])." directors!</info>");
                    foreach ($directorArray[$movieRow->UUID] as $director) {
                        $actorRow = Director::firstOrCreate(['name' => $director['name']]);
                        $movieRow->directors()->attach($actorRow);
                    }

                    /* @var $movieRow Movie */
                    if(isset($videoArray[$movieRow->UUID])) {
                        foreach ($videoArray[$movieRow->UUID] as $clip) {
                            /* @var $groupVideoRow GroupVideo */
                            $groupVideoRow = $movieRow->groupVideos()->create([
                                'title' => isset($clip->title)?$clip->title:'',
                                'type' => isset($clip->type)?$clip->type:'',
                                'thumbnail' => isset($clip->thumbnailUrl)?$clip->thumbnailUrl:'',
                                'url' => isset($clip->url)?$clip->url:''
                            ]);

                            if (isset($clip->alternatives)) {
                                if($trace)
                                    $this->output->writeln("<info>Adding ".count($clip->alternatives)." alternatives!</info>");
                                foreach ($clip->alternatives as $alternative) {
                                    $groupVideoRow->videos()->create([
                                        'quality' => isset($alternative->quality)?$alternative->quality:'',
                                        'url' => isset($alternative->url)?$alternative->url:''
                                    ]);
                                }
                            }
                        }
                    }
                }

                $moviesArray = [];
                $imagesArray = [];
                $actorsArray = [];
                $videoArray  = [];
            }

        }
    }

}