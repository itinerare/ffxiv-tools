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
     * Execute the job.
     */
    public function handle(): void {
        (new UniversalisCache)->updateCaches($this->world, $this->ids);
    }
}
