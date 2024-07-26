<?php

namespace App\Models;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\UpdateGameItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class GameRecipe extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipe_id', 'item_id', 'job', 'level', 'rlvl', 'stars', 'yield', 'ingredients',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ingredients' => 'array',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'game_recipes';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Retrieve recipe data for a given job.
     *
     * @param int $job
     *
     * @return bool
     */
    public function retrieveRecipes($job) {
        // Fetch Teamcraft's recipe dump from GitHub
        $response = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/staging/libs/data/src/lib/json/recipes-per-item.json');

        if ($response->successful()) {
            $response = json_decode($response->getBody(), true);

            // Affirm that the response is an array for safety
            if (is_array($response)) {
                // Cycle through the response and collect only recipes for the specified job
                $rawRecipes = collect();
                foreach ($response as $chunk) {
                    foreach ($chunk as $recipe) {
                        if ($recipe['job'] == $job && $recipe['expert'] == false && $recipe['qs'] == true) {
                            $rawRecipes->push($recipe);
                        }
                    }
                }

                // Filter recipes down further to those within per-xpac ranges
                foreach (config('ffxiv.crafting.ranges') as $key => $range) {
                    $recipes[$key] = isset($range['max']) ? $rawRecipes->where('rlvl', '>=', $range['min'])->where('rlvl', '<=', $range['max']) : $rawRecipes->where('rlvl', '>=', $range['min']);

                    $this->processRecipes($recipes[$key], $rawRecipes);
                }
            }
        }

        return true;
    }

    /**
     * Process recipe data for a given job and xpac.
     *
     * @param \Illuminate\Support\Collection $recipes
     * @param \Illuminate\Support\Collection $rawRecipes
     *
     * @return bool
     */
    public function processRecipes($recipes, $rawRecipes) {
        // Create an array to put items into to add to Game Items
        $items = [];

        // Cycle through recipes, recursively
        foreach ($recipes as $recipe) {
            $items = $this->recordRecipe($recipe, $rawRecipes, $items);
        }

        // Organize items and dispatch jobs to record them as necessary
        $items = collect($items)->unique()->chunk(100);
        foreach ($items as $chunk) {
            UpdateGameItem::dispatch($chunk);

            foreach (collect(config('ffxiv.data_centers'))->flatten()->toArray() as $world) {
                CreateUniversalisRecords::dispatch(strtolower($world), $chunk);
            }
        }

        return true;
    }

    /**
     * Process and record recipe information recursively.
     *
     * @param array                          $recipe
     * @param \Illuminate\Support\Collection $rawRecipes
     * @param array                          $items
     *
     * @return bool
     */
    public function recordRecipe($recipe, $rawRecipes, $items = []) {
        // Add the result item to items to record
        $items[] = $recipe['result'];

        if (!self::where('recipe_id', $recipe['id'])->exists()) {
            self::create([
                'recipe_id'   => $recipe['id'],
                'item_id'     => $recipe['result'],
                'job'         => $recipe['job'],
                'level'       => $recipe['lvl'],
                'rlvl'        => $recipe['rlvl'],
                'stars'       => $recipe['stars'],
                'yield'       => $recipe['yields'],
                'ingredients' => $recipe['ingredients'],
            ]);
        }

        foreach ($recipe['ingredients'] as $ingredient) {
            if ($rawRecipes->where('result', $ingredient['id'])->count()) {
                // If the ingredient is a precraft, record the recipe for it
                $items = $this->recordRecipe($rawRecipes->where('result', $ingredient['id'])->first(), $rawRecipes, $items);
            } else {
                $items[] = $ingredient['id'];
            }
        }

        return $items;
    }
}
