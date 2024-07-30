<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUniversalisCaches;
use App\Models\GameRecipe;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class CraftingController extends Controller {
    /**
     * Show the crafting profit calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCalculator(Request $request) {
        $request->validate([
            'character_job'         => ['nullable', Rule::in(array_keys((array) config('ffxiv.crafting.jobs')))],
            'purchase_precrafts'    => 'nullable|boolean',
            'prefer_hq'             => 'nullable|boolean',
            'include_crystals'      => 'nullable|boolean',
            'purchase_drops'        => 'nullable|boolean',
            'gatherable_preference' => 'nullable|in:0,1,2',
        ]);

        if ($request->all()) {
            // Assemble selected settings into an array for easy passing to price calculator function
            $settings = [
                'character_job'         => $request->get('character_job') ?? null,
                'purchase_precrafts'    => $request->get('purchase_precrafts') ?? 0,
                'prefer_hq'             => $request->get('prefer_hq') ?? 0,
                'include_crystals'      => $request->get('include_crystals') ?? 0,
                'purchase_drops'        => $request->get('purchase_drops') ?? 0,
                'gatherable_preference' => $request->get('gatherable_preference') ?? 0,
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
                $currentRange = $ranges->forPage($request->get('page') ?? 1, 1);

                foreach ($currentRange as $key => $range) {
                    $recipes[$key] = GameRecipe::job($request->get('character_job'))->with(['priceData' => function ($query) use ($request) {
                        $query->where('world', $request->get('world'))->limit(1);
                    }])->orderBy('rlvl', 'DESC')->orderBy('recipe_id', 'DESC')->where('rlvl', '>=', $range['min']);
                    if (isset($range['max'])) {
                        $recipes[$key] = $recipes[$key]->where('rlvl', '<=', $range['max']);
                    }
                    $recipes[$key] = $recipes[$key]->get();

                    // Gather ingredients and assemble common info for them
                    $ingredients[$key] = $recipes[$key]->pluck('ingredients')->transform(function ($ingredient, $key) {
                        return collect($ingredient)->transform(function ($item, $key) {
                            return $item['id'];
                        });
                    });
                    $ingredients[$key] = (new GameRecipe)->collectIngredients(request()->get('world'), $ingredients[$key]);

                    $rankedRecipes[$key] = collect($recipes[$key])->sortByDesc(function ($recipe) use ($settings, $ingredients, $key) {
                        $weight = 1 + (($recipe->priceData->first()->hq_sale_velocity ?? 0) / 100);

                        return ($recipe->calculateProfitPer($ingredients[$key], 1, $settings)['hq'] ?? 0) * $weight;
                    })->take(4);
                }
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
            'rankedRecipes' => $rankedRecipes ?? null,
            'paginator'     => isset($recipes) ? (new LengthAwarePaginator($recipes, $ranges->count(), 1))->withPath('/crafting')->appends($request->query()) : null,
            'recipes'       => $recipes ?? null,
            'ingredients'   => $ingredients ?? null,
        ]);
    }
}
