<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Search;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use JayI\Foundation\Support\ServiceProvider;
use JayI\Keystone\Domains\Search\Console\Commands\ReindexProductsCommand;
use JayI\Keystone\Domains\Search\Contracts\SearchEngine;
use JayI\Keystone\Domains\Search\Exceptions\UnsupportedSearchException;
use JayI\Keystone\Domains\Search\Services\DatabaseEngine;
use JayI\Keystone\Domains\Search\Services\ElasticsearchEngine;
use JayI\Keystone\Domains\Search\Services\ScoutEngine;
use JayI\Stretch\Stretch;
use Laravel\Scout\EngineManager;

class SearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Resolved per use, so the configured engine can change at runtime.
        $this->app->bind(SearchEngine::class, fn (Application $app): SearchEngine => $this->searchEngine($app));
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            ReindexProductsCommand::class,
        ]);
    }

    /**
     * The search engine named by `keystone.search.engine`.
     */
    private function searchEngine(Application $app): SearchEngine
    {
        $engine = $app->make('config')->get('keystone.search.engine', 'database');

        return match ($engine) {
            'database' => new DatabaseEngine,
            'elasticsearch' => class_exists(Stretch::class)
                ? new ElasticsearchEngine($app->make('stretch'), $app->make('config'))
                : throw UnsupportedSearchException::missingPackage('elasticsearch', 'jayi/stretch'),
            'scout' => class_exists(EngineManager::class)
                ? new ScoutEngine($app->make(EngineManager::class))
                : throw UnsupportedSearchException::missingPackage('scout', 'laravel/scout'),
            default => is_string($engine) && is_subclass_of($engine, SearchEngine::class)
                ? $app->make($engine)
                : throw new InvalidArgumentException(sprintf('Unknown Keystone search engine [%s].', is_scalar($engine) ? $engine : get_debug_type($engine))),
        };
    }
}
