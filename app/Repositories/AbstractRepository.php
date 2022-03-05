<?php


namespace App\Repositories;


class AbstractRepository
{
    private function calledMethod() {
        $e = new \Exception();
        $trace = $e->getTrace();
        $call = $trace[1];
        return $call['function'];
    }

    protected function cache($data, int $expire) {

        $key = static::class.'\\'.$this->calledMethod();
        $redis = app()->make('redis');
        if(!$redis->exists($key)) {
            $data = json_encode($data);
            $redis->set($key, $data);
            $redis->expire($key, $expire);
            return $data;
        } else {
            return $redis->get($key);
        }
    }
}