<?php

namespace App\Jobs;

use App\Models\UniversalisCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateUniversalisCacheChunk implements ShouldQueue {
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [5, 10];

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
        (new UniversalisCache)->updateCaches($this->world, $this->chunk);
    }
}
