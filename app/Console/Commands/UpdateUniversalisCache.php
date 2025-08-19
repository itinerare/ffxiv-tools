<?php

namespace App\Console\Commands;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\RecordRecipes;
use App\Jobs\UpdateGameItem;
use App\Jobs\UpdateUniversalisCaches;
use App\Models\GameItem;
use App\Models\UniversalisCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class UpdateUniversalisCache extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-universalis-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates cached data from Universalis in a queued fashion.';

    /**
     * Execute the console command.
     */
    public function handle(): int {
        // Gather all relevant game item IDs
        $items = collect((array) config('ffxiv.economy.diadem_items.node_data'))->flatten();

        $this->line('Initializing records as necessary...');
        if ($items->count() > GameItem::whereIn('item_id', $items->toArray())->whereNotNull('name')->count()) {
            $this->info('Queuing jobs to create game item records...');
            $gameItemsBar = $this->output->createProgressBar((int) ceil($items->count() / 100));
            $gameItemsBar->start();

            foreach ($items->chunk(100) as $chunk) {
                UpdateGameItem::dispatch($chunk);
                $gameItemsBar->advance();
            }

            $gameItemsBar->finish();
            $this->line("\n");
        }

        if ((collect((array) config('ffxiv.data_centers'))->flatten()->count() * $items->count()) > UniversalisCache::whereIn('item_id', $items->toArray())->count()) {
            $this->info('Queuing jobs to create Universalis cache records...');
            $universalisRecordsBar = $this->output->createProgressBar(collect((array) config('ffxiv.data_centers'))->flatten()->count() * (int) ceil($items->count() / 100));
            $universalisRecordsBar->start();

            foreach (collect((array) config('ffxiv.data_centers'))->flatten()->toArray() as $world) {
                if ($items->count() > UniversalisCache::world($world)->whereIn('item_id', $items->toArray())->count()) {
                    foreach ($items->chunk(100) as $chunk) {
                        CreateUniversalisRecords::dispatch($world, $chunk);
                        $universalisRecordsBar->advance();
                    }
                } else {
                    for ($i = 1; $i < ceil($items->count() / 100); $i++) {
                        $universalisRecordsBar->advance();
                    }
                }
            }
            $universalisRecordsBar->finish();
            $this->line("\n");
        }

        $this->info('Queueing jobs to retrieve recipes and associated items...');
        $craftingRecipesBar = $this->output->createProgressBar(count((array) config('ffxiv.economy.crafting.jobs')));
        $craftingRecipesBar->start();

        foreach (array_keys((array) config('ffxiv.economy.crafting.jobs')) as $jobId) {
            RecordRecipes::dispatch($jobId);
            $craftingRecipesBar->advance();
        }
        $craftingRecipesBar->finish();
        $this->line("\n");

        if (App::environment() == 'production') {
            // Queue jobs to update cached data from Universalis
            $this->info('Queuing jobs to update cached Universalis data...');
            $universalisBar = $this->output->createProgressBar(collect((array) config('ffxiv.data_centers'))->flatten()->count());
            foreach (collect((array) config('ffxiv.data_centers'))->flatten()->toArray() as $world) {
                UpdateUniversalisCaches::dispatch($world);
                $universalisBar->advance();
            }
            $universalisBar->finish();
            $this->line("\n");
        }

        $this->line('Pruning old records as necessary...');
        if (UniversalisCache::whereNotIn('item_id', GameItem::pluck('item_id')->toArray())->count()) {
            UniversalisCache::whereNotIn('item_id', GameItem::pluck('item_id')->toArray())->delete();
            $this->info('Pruned Universalis cache records...');
        }

        $this->line('Done!');

        return 0;
    }
}
