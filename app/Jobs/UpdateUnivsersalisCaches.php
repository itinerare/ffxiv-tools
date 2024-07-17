<?php

namespace App\Jobs;

use App\Models\UniversalisCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateUnivsersalisCaches implements ShouldQueue {
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param string $world
     * @param array  $ids
     */
    public function __construct(
        public $world,
        public $ids
    ) {
        $this->world = $world;
        $this->ids = $ids;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array {
        return [new WithoutOverlapping('update-universalis-cache')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        (new UniversalisCache)->updateCaches($this->world, $this->ids);
    }
}
