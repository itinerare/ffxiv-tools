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
     * Get the game item associated with this record.
     */
    public function gameItem() {
        return $this->belongsTo(GameItem::class, 'item_id', 'item_id');
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

    /**
     * Get the Universalis record for a recipe's output, for a given world.
     *
     * @param string $world
     *
     * @return UniversalisCache
     */
    public function getPriceData($world) {
        return UniversalisCache::world($world)->where('item_id', $this->item_id)->first();
    }

    /**
     * Format ingredient information and return as an array.
     *
     * @param string $world
     *
     * @return array
     */
    public function formatIngredients($world) {
        $ingredients = [];
        foreach ($this->ingredients as $ingredient) {
            $ingredients[$ingredient['id']] = [
                'gameItem'  => GameItem::where('item_id', $ingredient['id'])->first(),
                'priceData' => UniversalisCache::world($world)->where('item_id', $ingredient['id'])->first(),
                'recipe'    => self::where('item_id', $ingredient['id'])->first(),
                'amount'    => $ingredient['amount'],
            ];
        }

        return $ingredients;
    }

    /**
     * Calculate cost to make a given recipe.
     *
     * @param string     $world
     * @param array|null $settings
     * @param int        $quantity
     *
     * @return int
     */
    public function calculateCostPer($world, $settings = null, $quantity = 1) {
        $cost = 0;

        $ingredients = $this->formatIngredients($world);
        foreach ($ingredients as $item => $ingredient) {
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
                    $cost += $ingredient['recipe']->calculateCostPer($world, $settings, ceil(($ingredient['amount'] * $quantity) / $ingredient['recipe']->yield));
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
     * @param string     $world
     * @param bool       $hq
     * @param array|null $settings
     * @param int        $quantity
     *
     * @return int
     */
    public function calculateProfitPer($world, $hq = false, $settings = null, $quantity = 1) {
        $cost = ceil($this->calculateCostPer($world, $settings, $quantity) / $this->yield / $quantity);

        if ($hq) {
            $price = $this->getPriceData($world)->min_price_hq ?? 0;
        } else {
            $price = $this->getPriceData($world)->min_price_nq ?? 0;
        }

        return $price - $cost;
    }
}
