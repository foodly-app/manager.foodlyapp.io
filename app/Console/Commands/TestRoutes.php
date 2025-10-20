<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'routes:test {--filter= : Filter routes by name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all registered routes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filter = $this->option('filter');
        
        $this->info('🚀 Testing Routes...');
        $this->newLine();

        $routes = collect(Route::getRoutes())->filter(function ($route) use ($filter) {
            if (!$filter) {
                return true;
            }
            return str_contains($route->getName() ?? '', $filter);
        });

        $this->table(
            ['Method', 'URI', 'Name', 'Action'],
            $routes->map(function ($route) {
                return [
                    implode('|', $route->methods()),
                    $route->uri(),
                    $route->getName() ?? '-',
                    $route->getActionName()
                ];
            })->toArray()
        );

        $this->newLine();
        $this->info('Total Routes: ' . $routes->count());

        // Group by prefix
        $this->newLine();
        $this->info('📊 Routes by Prefix:');
        
        $grouped = $routes->groupBy(function ($route) {
            $uri = $route->uri();
            $parts = explode('/', $uri);
            return $parts[0] ?? 'root';
        });

        foreach ($grouped as $prefix => $prefixRoutes) {
            $this->line("  • {$prefix}: " . $prefixRoutes->count() . ' routes');
        }

        return Command::SUCCESS;
    }
}
