<?php

namespace App\Jobs;

use App\Models\GameRecipe;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordRecipes implements ShouldQueue {
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
     * @param int $jobId
     */
    public function __construct(
        public $jobId
    ) {
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        (new GameRecipe)->retrieveRecipes($this->jobId);
    }
}
