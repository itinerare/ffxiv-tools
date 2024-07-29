<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUnivsersalisCaches;
use App\Models\GameRecipe;
use Illuminate\Http\Request;

class CraftingController extends Controller {
    /**
     * Show the crafting profit calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCalculator(Request $request) {
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
                UpdateUnivsersalisCaches::dispatch($request->get('world'));

                foreach (config('ffxiv.crafting.ranges') as $key => $range) {
                    $recipes[$key] = GameRecipe::job($request->get('character_job'))->orderBy('rlvl', 'DESC')->orderBy('recipe_id', 'DESC')->where('rlvl', '>=', $range['min']);
                    if (isset($range['max'])) {
                        $recipes[$key] = $recipes[$key]->where('rlvl', '<=', $range['max']);
                    }
                    $recipes[$key] = $recipes[$key]->get();

                    $rankedRecipes[$key] = collect($recipes[$key])->sortByDesc(function ($recipe, $key) use ($request, $settings) {
                        $weight = 1 + (($recipe->getPriceData($request->get('world'))->hq_sale_velocity ?? 0) / 100);

                        return $recipe->calculateProfitPer($request->get('world'), 1, $settings ?? null) * $weight;
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
            'recipes'       => $recipes ?? null,
        ]);
    }
}
