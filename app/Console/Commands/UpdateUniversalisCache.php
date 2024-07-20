<?php

namespace App\Console\Commands;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\UpdateGameItem;
use App\Jobs\UpdateUnivsersalisCaches;
use App\Models\GameItem;
use App\Models\UniversalisCache;
use Illuminate\Console\Command;

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
    public function handle() {
        // Gather all relevant game item IDs
        $items = collect(config('ffxiv.diadem_items.node_data'))->flatten();

        $this->line('Initializing records as necessary...');
        if ($items->count() > GameItem::whereIn('item_id', $items->toArray())->whereNotNull('name')->count()) {
            $this->info('Queuing jobs to create game item records...');
            $gameItemsBar = $this->output->createProgressBar(ceil($items->count() / 20));
            $gameItemsBar->start();

            foreach ($items->chunk(20) as $chunk) {
                UpdateGameItem::dispatch($chunk);
                $gameItemsBar->advance();
            }

            $gameItemsBar->finish();
            $this->line("\n");
        }

        if ((collect(config('ffxiv.data_centers'))->flatten()->count() * $items->count()) > UniversalisCache::whereIn('item_id', $items->toArray())->count()) {
            $this->info('Queuing jobs to create Universalis cache records...');
            $universalisRecordsBar = $this->output->createProgressBar(collect(config('ffxiv.data_centers'))->flatten()->count() * ($items->count() / 100));
            $universalisRecordsBar->start();

            foreach (collect(config('ffxiv.data_centers'))->flatten()->toArray() as $world) {
                if ($items->count() > UniversalisCache::world(strtolower($world))->whereIn('item_id', $items->toArray())->count()) {
                    foreach ($items->chunk(100) as $chunk) {
                        CreateUniversalisRecords::dispatch(strtolower($world), $chunk);
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

        // Queue jobs to update cached data from Universalis
        $this->info('Queuing jobs to update cached Universalis data...');
        $universalisBar = $this->output->createProgressBar(collect(config('ffxiv.data_centers'))->flatten()->count());
        foreach (collect(config('ffxiv.data_centers'))->flatten()->toArray() as $world) {
            UpdateUnivsersalisCaches::dispatch(strtolower($world), $items);
            $universalisBar->advance();
        }
        $universalisBar->finish();
        $this->line("\n");

        $this->line('Pruning old records as necessary...');
        if (GameItem::whereNotIn('item_id', $items->toArray())->count()) {
            GameItem::whereNotIn('item_id', $items->toArray())->delete();
            $this->info('Pruned game item records...');
        }
        if (UniversalisCache::whereNotIn('item_id', $items->toArray())->count()) {
            UniversalisCache::whereNotIn('item_id', $items->toArray())->delete();
            $this->info('Pruned Universalis cache records...');
        }

        $this->info('Done!');
    }
}