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
        'recipe_id', 'item_id', 'job', 'level', 'rlvl', 'stars', 'yield', 'ingredients', 'can_hq',
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
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'gameItem',
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

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the game item associated with this recipe.
     */
    public function gameItem() {
        return $this->belongsTo(GameItem::class, 'item_id', 'item_id');
    }

    /**
     * Get the Universalis records associated with this recipe.
     */
    public function priceData() {
        return $this->hasMany(UniversalisCache::class, 'item_id', 'item_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to include recipes for a given job.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int                                   $job
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJob($query, $job) {
        return $query->where('job', $job);
    }

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
        $items = collect($items)->unique();
        $gameItems = $items->filter(function ($item, $key) {
            return !GameItem::where('item_id', $item)->exists();
        })->chunk(100);
        foreach ($gameItems as $chunk) {
            UpdateGameItem::dispatch($chunk);
        }

        $universalisItems = $items->filter(function ($item, $key) {
            return UniversalisCache::where('item_id', $item)->count() < collect(config('ffxiv.data_centers'))->flatten()->count();
        })->chunk(100);
        foreach ($universalisItems as $chunk) {
            // Any theoretical number of jobs, when multiplied by the number of supported worlds,
            // is large enough that it's worth only dispatching jobs if necessary
            if ($chunk->count()) {
                foreach (collect(config('ffxiv.data_centers'))->flatten()->toArray() as $world) {
                    CreateUniversalisRecords::dispatch(strtolower($world), $chunk);
                }
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
                'can_hq'      => $recipe['hq'],
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

    /**
     * Recursively collect all ingredients for a given set of recipes.
     *
     * @param string                         $world
     * @param \Illuminate\Support\Collection $ingredients
     * @param \Illuminate\Support\Collection $existing
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectIngredients($world, $ingredients, $existing = null) {
        $ingredients = $ingredients->flatten()->unique();

        if ($existing) {
            // Filter out ingredients already in the parent collection
            $ingredients = $ingredients->filter(function ($value) use ($existing) {
                return !$existing->has($value);
            });
        }

        $ingredients = $ingredients->mapWithKeys(function ($item, $key) use ($world) {
            $recipe = GameRecipe::where('item_id', $item)->with(['priceData' => function ($query) use ($world) {
                $query->where('world', $world)->limit(1);
            }])->first();

            if ($recipe) {
                // If the ingredient has a recipe, take price and game data loaded with it
                return [$item => [
                    'recipe'    => $recipe,
                    'priceData' => $recipe->priceData->first(),
                    'gameItem'  => $recipe->gameItem ?? null,
                ]];
            }

            // Otherwise load the price and game data as one query
            $priceData = UniversalisCache::world($world)->with('gameItem')->where('item_id', $item)->first();

            return [$item => [
                'recipe'    => $recipe,
                'priceData' => $priceData,
                'gameItem'  => $priceData->gameItem ?? null,
            ]];
        });

        $precrafts = $ingredients->whereNotNull('recipe');
        if ($precrafts->count()) {
            $precraftIngredients = $precrafts->pluck('recipe.ingredients')->transform(function ($ingredient, $key) {
                return collect($ingredient)->transform(function ($item, $key) {
                    return $item['id'];
                });
            });

            $precraftIngredients = $this->collectIngredients($world, $precraftIngredients, $existing ?? $ingredients);
        }

        return $ingredients->union($precraftIngredients ?? []);
    }

    /**
     * Format ingredient information and return as an array.
     *
     * @param array $ingredients
     *
     * @return array
     */
    public function formatIngredients($ingredients) {
        $ingredientList = [];

        foreach ($this->ingredients as $ingredient) {
            $ingredientList[$ingredient['id']] = ($ingredients[$ingredient['id']] ?? []) + ['amount' => $ingredient['amount']];
        }

        return $ingredientList;
    }

    /**
     * Calculate cost to make a given recipe.
     *
     * @param \Illuminate\Support\Collection $ingredients
     * @param array|null                     $settings
     * @param int                            $quantity
     *
     * @return int
     */
    public function calculateCostPer($ingredients, $settings = null, $quantity = 1) {
        $cost = 0;

        $ingredientList = $this->formatIngredients($ingredients);
        foreach ($ingredientList as $item => $ingredient) {
            // Skip shard/crystal/clusters in recipes if not included in calculations
            if ((!isset($settings['include_crystals']) || !$settings['include_crystals']) && in_array($item, (array) config('ffxiv.crafting.crystals'))) {
                continue;
            }

            // Skip mob drops if not purchasing them
            if ((!isset($settings['purchase_drops']) || !$settings['purchase_drops']) && $ingredient['gameItem']?->is_mob_drop) {
                continue;
            }

            if (isset($settings['gatherable_preference']) && $settings['gatherable_preference'] > 0 && (isset($ingredient['gameItem']->gather_data) && $ingredient['gameItem']->gather_data) && !in_array($item, (array) config('ffxiv.crafting.crystals'))) {
                if ($settings['gatherable_preference'] == 1 && !$ingredient['gameItem']->gather_data['perceptionReq']) {
                    // Skip special gatherables if gathering only unrestricted mats
                    continue;
                } elseif ($settings['gatherable_preference'] == 2) {
                    // Skip all gatherables if gathering everything
                    continue;
                }
            }

            // Handle precraft-related calculations
            if ($ingredient['recipe']) {
                if (isset($settings['purchase_precrafts']) && $settings['purchase_precrafts'] && $settings['prefer_hq']) {
                    // If both purchasing precrafts and prefering HQ materials, include HQ precraft price instead
                    $cost += $ingredient['priceData']?->min_price_hq * $ingredient['amount'];
                    continue;
                } elseif (!isset($settings['purchase_precrafts']) || !$settings['purchase_precrafts']) {
                    // If not purchasing precrafts, include material costs recursively
                    $cost += $ingredient['recipe']->calculateCostPer($ingredients, $settings, ceil(($ingredient['amount'] * $quantity) / $ingredient['recipe']->yield));
                    continue;
                }
            }

            $cost += $ingredient['priceData']?->min_price_nq * ($ingredient['amount'] * $quantity);
        }

        return $cost;
    }

    /**
     * Calculate profit from a given recipe.
     *
     * @param array      $ingredients
     * @param bool       $hq
     * @param array|null $settings
     * @param int        $quantity
     *
     * @return array
     */
    public function calculateProfitPer($ingredients, $hq = false, $settings = null, $quantity = 1) {
        $priceData = $this->priceData->first();
        if (!$priceData) {
            return null;
        }

        $cost = ceil(($this->calculateCostPer($ingredients, $settings, $quantity) / $this->yield) / $quantity);

        return [
            'nq' => $priceData->min_price_nq - $cost,
            'hq' => $hq ? $priceData->min_price_hq - $cost : null,
        ];
    }

    /**
     * Calculate profit from a given recipe; returns a formatted string.
     *
     * @param array      $ingredients
     * @param array|null $settings
     * @param int        $quantity
     *
     * @return string|null
     */
    public function displayProfitPer($ingredients, $settings = null, $quantity = 1) {
        $profitsPer = $this->calculateProfitPer($ingredients, $this->can_hq, $settings, $quantity);

        return ($this->can_hq ? number_format($profitsPer['hq']).' <small>(HQ)</small> / ' : '').number_format($profitsPer['nq']).($this->can_hq ? ' <small>(NQ)</small>' : '').' Gil';
    }
}
