<?php

namespace App\Jobs;

use App\Models\UniversalisCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreateUniversalisRecords implements ShouldQueue {
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
     * @param string                         $world
     * @param \Illuminate\Support\Collection $chunk
     */
    public function __construct(
        public $world,
        public $chunk
    ) {
        $this->world = $world;
        $this->chunk = $chunk;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        foreach ($this->chunk as $item) {
            (new UniversalisCache)->recordItem($this->world, $item);
        }
    }
}
