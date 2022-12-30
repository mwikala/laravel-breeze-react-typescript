<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Tightenco\Ziggy\Ziggy;

class GenerateZiggyTypescriptDefinitions extends Command
{
    protected $signature = 'ziggy:typescript';

    protected $description = 'Generate TS definitions for ziggy';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $path = './resources/js/ziggy-shims.d.ts';
        $generatedRoutes = $this->generate();

        $this->makeDirectory($path);
        $this->files->put(base_path($path), $generatedRoutes);

        $this->info('File generated!');
    }

    private function generate(): string
    {
        $ziggy = (new Ziggy(false, null));
        $collectedRoutes = collect($ziggy->toArray()['routes']);
        $routes = $collectedRoutes
            ->map(function ($route, $key) {
                $methods = json_encode($route['methods'] ?? []);
                return <<<TYPESCRIPT
    "{$key}": {
        "uri": "{$route['uri']}",
        "methods": {$methods},
    },
TYPESCRIPT;

            })
            ->join("\n");

        $params = $collectedRoutes->map(function ($route, $key) {
            $matches = [];
            preg_match_all('/(?<=\{)(.*?)(?=\})/', $route['uri'], $matches);
            if (!count($matches) || !count($matches[0])) {
                return <<<TYPESCRIPT
    "{$key}": {

    },
TYPESCRIPT;
            } else {
                $unencodedParams = collect($matches[0])->reduce(function ($carry, $match) {
                    $carry[$match] = $match;
                    return $carry;
                }, []);

                $params = json_encode($unencodedParams);
                return <<<TYPESCRIPT
    "{$key}":
        {$params}
    ,
TYPESCRIPT;
            }
        })
            ->join("\n");
//Log::debug($params);
        return <<<TYPESCRIPT
import {Config, Router} from "ziggy-js";

type LaravelRoutes = {
    {$routes}
}
const LaravelParams = {
    ${params}
}
declare global {
    declare interface ZiggyLaravelRoutes extends LaravelRoutes {}
    declare function route(): Router;
    declare function route<RouteKey extends keyof LaravelRoutes>(name: RouteKey, params?: LaravelRoutes[RouteKey], absolute?: boolean, customZiggy?: Config): string;
}
export { LaravelRoutes, LaravelParams };
TYPESCRIPT;
    }

    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname(base_path($path)))) {
            $this->files->makeDirectory(dirname(base_path($path)), 0755, true, true);
        }

        return $path;
    }
}
