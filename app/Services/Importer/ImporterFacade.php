<?php


namespace App\Services\Importer;


use Illuminate\Support\Facades\Facade;

class ImporterFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'importer'; }
}