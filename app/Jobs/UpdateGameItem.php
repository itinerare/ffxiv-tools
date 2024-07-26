<?php

namespace App\Jobs;

use App\Models\GameItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UpdateGameItem implements ShouldQueue {
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
     * @param \Illuminate\Support\Collection $chunk
     */
    public function __construct(
        public $chunk
    ) {
        $this->chunk = $chunk;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array {
        return [new WithoutOverlapping('update-game-item')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        (new GameItem)->recordItem($this->chunk);
    }
}
