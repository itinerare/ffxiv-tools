<?php

namespace App\Console\Commands;

use App\Models\GameRecipe;
use Illuminate\Console\Command;

class PruneErroneousRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-erroneous-recipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prunes recipes with values outside of expected.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipes = GameRecipe::all();

        foreach ($recipes as $recipe) {
            if ($recipe->yield > 10) {
                $recipe->delete();
                continue;
            }

            foreach ($recipe->ingredients as $ingredient) {
                if ($ingredient['amount'] < 1) {
                    $recipe->delete();
                    continue;
                }
            }
        }
    }
}
