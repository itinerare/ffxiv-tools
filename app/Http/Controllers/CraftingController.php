<?php

namespace App\Http\Controllers;

use App\Models\GameRecipe;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class CraftingController extends Controller {
    /**
     * Show the crafting profit calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCraftingCalculator(Request $request) {
        $inputs = [
            'world'                 => [
                'nullable', 'string',
                Rule::in(collect((array) config('ffxiv.data_centers'))->flatten()->toArray()),
            ],
            'character_job'         => ['nullable', Rule::in(array_keys((array) config('ffxiv.crafting.jobs')))],
            'no_master'             => 'nullable|boolean',
            'min_profit'            => 'nullable|numeric',
            'purchase_precrafts'    => 'nullable|boolean',
            'prefer_hq'             => 'nullable|boolean',
            'include_crystals'      => 'nullable|boolean',
            'include_aethersands'   => 'nullable|boolean',
            'purchase_drops'        => 'nullable|boolean',
            'gatherable_preference' => 'nullable|in:0,1,2',
            'fish_preference'       => 'nullable|in:0,1,2',
            'shop_preference'       => 'nullable|in:0,1,2',
        ];
        $request->validate($inputs);

        $request = $this->handleSettingsCookie($request, 'craftingSettings', $inputs);

        // Assemble selected settings into an array for easy passing to price calculator function
        $settings = [
            'character_job'         => $request->get('character_job') ?? null,
            'min_profit'            => $request->get('min_profit') ?? null,
            'no_master'             => $request->get('no_master') ?? 0,
            'purchase_precrafts'    => $request->get('purchase_precrafts') ?? 0,
            'prefer_hq'             => $request->get('prefer_hq') ?? 0,
            'include_crystals'      => $request->get('include_crystals') ?? 0,
            'include_aethersands'   => $request->get('include_aethersands') ?? 0,
            'purchase_drops'        => $request->get('purchase_drops') ?? 0,
            'gatherable_preference' => $request->get('gatherable_preference') ?? 0,
            'fish_preference'       => $request->get('fish_preference') ?? 0,
            'shop_preference'       => $request->get('shop_preference') ?? 0,
        ];

        if ($request->get('world')) {
            if ($settings['character_job'] && in_array($settings['character_job'], array_keys((array) config('ffxiv.crafting.jobs')))) {
                // Check and, if necessary, update cached data
                $universalisUpdate = $this->checkUniversalisCache($request->get('world'));

                $ranges = collect(config('ffxiv.crafting.ranges'));
                $currentRange = $ranges->forPage($request->get('page') ?? 1, 1)->first();

                $recipes = GameRecipe::job($settings['character_job'])->with(['priceData' => function ($query) use ($request) {
                    $query->where('world', $request->get('world'))->limit(1);
                }])->orderBy('rlvl', 'DESC')->orderBy('recipe_id', 'DESC')->where('rlvl', '>=', $currentRange['min']);
                if (isset($currentRange['max'])) {
                    $recipes = $recipes->where('rlvl', '<=', $currentRange['max']);
                }
                if ($settings['no_master']) {
                    $recipes = $recipes->where('stars', 0);
                }

                $recipes = $recipes->get();

                // Gather ingredients and assemble common info for them
                $ingredients = $recipes->pluck('ingredients')->transform(function ($ingredient, $key) {
                    return collect($ingredient)->transform(function ($item, $key) {
                        return $item['id'];
                    });
                });
                $ingredients = (new GameRecipe)->collectIngredients(request()->get('world'), $ingredients)->toArray();

                $rankedRecipes = collect($recipes)->filter(function ($recipe) use ($settings, $ingredients) {
                    if (!$recipe->priceData->first() || !$recipe->priceData->first()->filterRecommendations($recipe->can_hq)) {
                        return false;
                    }

                    $profit = $recipe->calculateProfitPer($ingredients, $recipe->can_hq, $settings);
                    if ($profit) {
                        if ($recipe->can_hq) {
                            if ($profit['hq'] <= 0 || (($settings['min_profit'] ?? false) && $profit['hq'] < $settings['min_profit'])) {
                                return false;
                            }
                        } else {
                            if ($profit['nq'] <= 0 || (($settings['min_profit'] ?? false) && $profit['nq'] < $settings['min_profit'])) {
                                return false;
                            }
                        }
                    } else {
                        return false;
                    }

                    return true;
                })->sortByDesc(function ($recipe) use ($settings, $ingredients) {
                    return $recipe->priceData->first()->calculateWeight($recipe->can_hq, $recipe->calculateProfitPer($ingredients, $recipe->can_hq, $settings));
                })->take(4);
            }
        }

        return view('crafting.index', [
            'dataCenters'       => config('ffxiv.data_centers'),
            'world'             => $request->get('world') ?? null,
            'settings'          => $settings,
            'paginator'         => isset($recipes) ? (new LengthAwarePaginator($recipes, count((array) config('ffxiv.crafting.ranges')), 1))->withPath('/crafting')->appends($request->query()) : null,
            'rankedRecipes'     => $rankedRecipes ?? null,
            'ingredients'       => $ingredients ?? null,
            'universalisUpdate' => $universalisUpdate ?? null,
        ]);
    }

    /**
     * Show the gathering profit calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getGatheringCalculator(Request $request) {
        $inputs = [
            'world'           => [
                'nullable', 'string',
                Rule::in(collect((array) config('ffxiv.data_centers'))->flatten()->toArray()),
            ],
            'min_price'           => 'nullable|numeric',
            'include_limited'     => 'nullable|boolean',
            'include_aethersands' => 'nullable|boolean',
            'fish_preference'     => 'nullable|in:0,1,2',
        ];
        $request->validate($inputs);

        $request = $this->handleSettingsCookie($request, 'gatheringSettings', $inputs);

        if ($request->get('world')) {
            $ranges = collect(config('ffxiv.crafting.ranges'));
            $currentRange = $ranges->forPage($request->get('page') ?? 1, 1)->first();

            // Check and, if necessary, update cached data
            $universalisUpdate = $this->checkUniversalisCache($request->get('world'));

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
                ->where(function ($ingredient) use ($request) {
                    if ($request->get('include_aethersands') && preg_match('/[a-zA-Z]+ Aethersand/', $ingredient['gameItem']?->name)) {
                        return true;
                    } elseif ($ingredient['gameItem']?->gather_data) {
                        return true;
                    }

                    return false;
                })
                ->whereNotNull('priceData')->sortByDesc('priceData.min_price_nq');

            // Filter out fish
            if (!$request->get('fish_preference')) {
                $items = $items->where('gameItem.gather_data.is_fish', false);
            } elseif ($request->get('fish_preference') == 1) {
                // Filter out restricted fish, ignoring other mats
                $items = $items->where(function ($item) {
                    if (!$item['gameItem']->gather_data || !$item['gameItem']->gather_data['is_fish'] || $item['gameItem']->gather_data['folklore'] == null) {
                        return true;
                    }

                    return false;
                });
            }

            // Filter down to only unrestricted mats, ignoring fish
            if (!$request->get('include_limited')) {
                $items = $items->where(function ($item) {
                    if (!$item['gameItem']->gather_data || $item['gameItem']->gather_data['is_fish'] || $item['gameItem']->gather_data['perception_req'] == 0) {
                        return true;
                    }

                    return false;
                });
            }

            $rankedItems = $items->filter(function ($item, $itemId) use ($request) {
                if (!$item['priceData'] || !$item['priceData']->filterRecommendations(false)) {
                    return false;
                }

                if (in_array($itemId, (array) config('ffxiv.crafting.crystals'))) {
                    return false;
                }

                if ($item['priceData']->min_price_nq <= 0 || ($request->get('min_price') && $item['priceData']->min_price_nq < $request->get('min_price'))) {
                    return false;
                }

                return true;
            })->sortByDesc(function ($item) {
                return $item['priceData']->calculateWeight();
            })->take(8);
        }

        return view('gathering.index', [
            'dataCenters'       => config('ffxiv.data_centers'),
            'world'             => $request->get('world') ?? null,
            'paginator'         => isset($items) ? (new LengthAwarePaginator($items, count((array) config('ffxiv.crafting.ranges')), 1))->withPath('/gathering')->appends($request->query()) : null,
            'rankedItems'       => $rankedItems ?? null,
            'universalisUpdate' => $universalisUpdate ?? null,
        ]);
    }

    /**
     * Fetches a given file from Teamcraft's dumps.
     *
     * @param string $filename
     *
     * @return \Illuminate\Http\Client\Response $request
     */
    public function teamcraftDataRequest($filename) {
        $request = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/refs/heads/staging/libs/data/src/lib/json/'.$filename);

        return $request;
    }
}
