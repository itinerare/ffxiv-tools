<?php

namespace App\Console\Commands;

use App\Models\GameRecipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AddHqToRecipes extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-hq-to-recipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        // Get recipes where HQ is set to 1; this may be correct, or it may be due to absent information
        $recipes = GameRecipe::where('can_hq', 1);

        $this->info('Processing '.$recipes->count().' recipe'.($recipes->count() != 1 ? 's' : '').'...');
        if ($recipes->count()) {
            $progressBar = $this->output->createProgressBar($recipes->count());
            $progressBar->start();

            foreach ($recipes->get() as $recipe) {
                // Make a request to XIVAPI
                $response = Http::retry(3, 100, throw: false)->get('https://xivapi.com/recipe/'.$recipe->recipe_id);

                if ($response->successful()) {
                    $response = json_decode($response->getBody(), true);

                    // Affirm that the response is an array for safety
                    if (is_array($response)) {
                        $recipe->update([
                            'can_hq' => $response['CanHq'],
                        ]);
                    }
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->line("\n".'Done!');
        } else {
            $this->line('No recipes to process!');
        }
    }
}
