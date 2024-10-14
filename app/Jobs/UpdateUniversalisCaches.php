<?php

namespace App\Jobs;

use App\Models\UniversalisCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;

class UpdateUniversalisCaches implements ShouldQueue {
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param string                              $world
     * @param \Illuminate\Support\Collection|null $items
     */
    public function __construct(
        public $world,
        public $items = null
    ) {
        $this->world = $world;
        $this->items = $items;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array {
        return [(new RateLimited('universalis-cache-updates'))->dontRelease()];
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        if (!$this->items) {
            $this->items = UniversalisCache::world($this->world)->pluck('item_id');
        }

        // Filter down to only items that have not been updated recently, or without price data
        $this->items = UniversalisCache::world($this->world)->whereIn('item_id', $this->items)->needsUpdate()->get();

        // Only make make request(s) to Universalis if there are items to update
        if ($this->items->count()) {
            foreach ($this->items->chunk(100) as $chunk) {
                UpdateUniversalisCacheChunk::dispatch($this->world, $chunk);
            }
        }
    }
}
