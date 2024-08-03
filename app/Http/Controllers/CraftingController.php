<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUniversalisCaches;
use App\Models\GameRecipe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class CraftingController extends Controller {
    /**
     * Show the crafting profit calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCraftingCalculator(Request $request) {
        $request->validate([
            'character_job'         => ['nullable', Rule::in(array_keys((array) config('ffxiv.crafting.jobs')))],
            'no_master'             => 'nullable|boolean',
            'purchase_precrafts'    => 'nullable|boolean',
            'prefer_hq'             => 'nullable|boolean',
            'include_crystals'      => 'nullable|boolean',
            'include_aethersands'   => 'nullable|boolean',
            'purchase_drops'        => 'nullable|boolean',
            'gatherable_preference' => 'nullable|in:0,1,2',
            'shop_preference'       => 'nullable|in:0,1,2',
        ]);

        if ($request->all()) {
            // Assemble selected settings into an array for easy passing to price calculator function
            $settings = [
                'character_job'         => $request->get('character_job') ?? null,
                'no_master'             => $request->get('no_master') ?? 0,
                'purchase_precrafts'    => $request->get('purchase_precrafts') ?? 0,
                'prefer_hq'             => $request->get('prefer_hq') ?? 0,
                'include_crystals'      => $request->get('include_crystals') ?? 0,
                'include_aethersands'   => $request->get('include_aethersands') ?? 0,
                'purchase_drops'        => $request->get('purchase_drops') ?? 0,
                'gatherable_preference' => $request->get('gatherable_preference') ?? 0,
                'shop_preference'       => $request->get('shop_preference') ?? 0,
            ];
        }

        if ($request->get('world')) {
            // Validate that the world exists
            $isValid = false;
            foreach (config('ffxiv.data_centers') as $dataCenters) {
                foreach ($dataCenters as $dataCenter) {
                    if (in_array(ucfirst($request->get('world')), $dataCenter)) {
                        $isValid = true;
                        break;
                    }
                }
            }

            if ($isValid && $request->get('character_job') && in_array($request->get('character_job'), array_keys((array) config('ffxiv.crafting.jobs')))) {
                // Check and, if necessary, update cached data
                UpdateUniversalisCaches::dispatch($request->get('world'));

                $ranges = collect(config('ffxiv.crafting.ranges'));
                $currentRange = $ranges->forPage($request->get('page') ?? 1, 1)->first();

                $recipes = GameRecipe::job($request->get('character_job'))->with(['priceData' => function ($query) use ($request) {
                    $query->where('world', $request->get('world'))->limit(1);
                }])->orderBy('rlvl', 'DESC')->orderBy('recipe_id', 'DESC')->where('rlvl', '>=', $currentRange['min']);
                if (isset($currentRange['max'])) {
                    $recipes = $recipes->where('rlvl', '<=', $currentRange['max']);
                }
                if ($request->get('no_master')) {
                    $recipes = $recipes->where('stars', 0);
                }

                $recipes = $recipes->get();

                // Gather ingredients and assemble common info for them
                $ingredients = $recipes->pluck('ingredients')->transform(function ($ingredient, $key) {
                    return collect($ingredient)->transform(function ($item, $key) {
                        return $item['id'];
                    });
                });
                $ingredients = (new GameRecipe)->collectIngredients(request()->get('world'), $ingredients);

                $rankedRecipes = collect($recipes)->sortByDesc(function ($recipe) use ($settings, $ingredients) {
                    // Don't recommend recipes that have no sale velocity
                    if (($recipe->priceData->first()->hq_sale_velocity ?? 0) == 0 && ($recipe->priceData->first()->nq_sale_velocity ?? 0) == 0) {
                        return 0;
                    }
                    // Don't recommend recipes with a last upload older 12 hours
                    if ($recipe->priceData->first()->last_upload_time < Carbon::now()->subHours(12)) {
                        return 0;
                    }

                    $weight = 1;

                    if ($recipe->can_hq) {
                        $weight += (($recipe->calculateProfitPer($ingredients, 1, $settings)['hq'] ?? 0) / 1000);

                        return ($recipe->priceData->first()->hq_sale_velocity ?? 0) * $weight;
                    }

                    $weight += (($recipe->calculateProfitPer($ingredients, 1, $settings)['nq'] ?? 0) / 1000);

                    return ($recipe->priceData->first()->nq_sale_velocity ?? 0) * $weight;
                })->take(4);
            } elseif ($isValid) {
                // Do nothing, and do not unset the selected world
            } else {
                // If the world name is invalid, unset it
                // so that the frontend treats it as not having selected anything
                $request->offsetUnset('world');
            }
        }

        return view('crafting.index', [
            'dataCenters'   => config('ffxiv.data_centers'),
            'world'         => $request->get('world') ?? null,
            'settings'      => $settings ?? null,
            'paginator'     => isset($recipes) ? (new LengthAwarePaginator($recipes, $ranges->count(), 1))->withPath('/crafting')->appends($request->query()) : null,
            'rankedRecipes' => $rankedRecipes ?? null,
            'ingredients'   => $ingredients ?? null,
        ]);
    }

    /**
     * Show the gathering profit calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGatheringCalculator(Request $request) {
        $request->validate([
            'character_job'   => ['nullable', Rule::in(array_keys((array) config('ffxiv.gathering.jobs')))],
            'include_limited' => 'nullable|in:0,1,2',
        ]);

        if ($request->all()) {
            // Assemble selected settings into an array for easy passing to price calculator function
            $settings = [
                'character_job'   => $request->get('character_job') ?? null,
                'include_limited' => $request->get('include_limited') ?? 0,
            ];
        }

        if ($request->get('world')) {
            // Validate that the world exists
            $isValid = false;
            foreach (config('ffxiv.data_centers') as $dataCenters) {
                foreach ($dataCenters as $dataCenter) {
                    if (in_array(ucfirst($request->get('world')), $dataCenter)) {
                        $isValid = true;
                        break;
                    }
                }
            }

            $ranges = collect(config('ffxiv.crafting.ranges'));
            $currentRange = $ranges->forPage($request->get('page') ?? 1, 1)->first();

            if ($isValid) {
                // Check and, if necessary, update cached data
                UpdateUniversalisCaches::dispatch($request->get('world'));

                // Collect recipes so as to collect their ingredients
                $recipes = GameRecipe::orderBy('rlvl', 'DESC')->orderBy('recipe_id', 'DESC')->where('rlvl', '>=', $currentRange['min']);
                if (isset($currentRange['max'])) {
                    $recipes = $recipes->where('rlvl', '<=', $currentRange['max']);
                }
                $recipes = $recipes->get();

                // Gather ingredients and assemble common info for them
                $ingredients = $recipes->pluck('ingredients')->transform(function ($ingredient, $key) {
                    return collect($ingredient)->transform(function ($item, $key) {
                        return $item['id'];
                    });
                });
                $items = (new GameRecipe)->collectIngredients(request()->get('world'), $ingredients)
                    ->whereNotNull('gameItem.gather_data')
                    ->whereNotNull('priceData')->sortByDesc('priceData.min_price_nq');

                // Filter down to only unrestricted mats
                if (!$request->get('include_limited')) {
                    $items = $items->where('gameItem.gather_data.perceptionReq', 0);
                }

                $rankedItems = $items->sortByDesc(function ($item, $itemId) {
                    // Do not recommend items that have no sale velocity
                    if (($item['priceData']->nq_sale_velocity ?? 0) == 0) {
                        return 0;
                    }

                    // Do not recommend crystals
                    if (in_array($itemId, (array) config('ffxiv.crafting.crystals'))) {
                        return 0;
                    }

                    $weight = 1;
                    $weight += (($item['priceData']->nq_sale_velocity ?? 0) / 100);

                    return $item['priceData']->min_price_nq * $weight;
                })->take(8);
            } else {
                // If the world name is invalid, unset it
                // so that the frontend treats it as not having selected anything
                $request->offsetUnset('world');
            }
        }

        return view('gathering.index', [
            'dataCenters'   => config('ffxiv.data_centers'),
            'world'         => $request->get('world') ?? null,
            'paginator'     => isset($items) ? (new LengthAwarePaginator($items ?? [], $ranges->count(), 1))->withPath('/gathering')->appends($request->query()) : null,
            'rankedItems'   => $rankedItems ?? null,
        ]);
    }
}
