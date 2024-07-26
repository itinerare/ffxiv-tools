<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateUnivsersalisCaches;
use App\Models\GameItem;
use App\Models\UniversalisCache;
use Illuminate\Http\Request;

class DiademController extends Controller {
    /**
     * Show the diadem page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDiadem(Request $request) {
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

            $diademItems = collect(config('ffxiv.diadem_items.node_data'))->flatten();

            if ($isValid && $diademItems->count() == UniversalisCache::world($request->get('world'))->whereIn('item_id', $diademItems->toArray())->count() && $diademItems->count() == GameItem::whereIn('item_id', $diademItems->toArray())->count()) {
                // Check and, if necessary, update cached data
                UpdateUnivsersalisCaches::dispatch($request->get('world'));

                // Get cached item records
                $items = UniversalisCache::world($request->get('world'))->whereIn('item_id', $diademItems)->get();

                // Collect individual node data
                $availableItems = [];
                foreach (config('ffxiv.diadem_items.node_data.BTN') as $node) {
                    $availableItems['BTN'][] = collect($node);
                }
                foreach (config('ffxiv.diadem_items.node_data.MIN') as $node) {
                    $availableItems['MIN'][] = collect($node);
                }
                $availableItems = collect($availableItems);

                // Assemble a list of available items ranked by price for each class
                // This provides a very simple overview
                foreach ($availableItems as $class => $chunk) {
                    foreach ($chunk as $node) {
                        foreach ($node as $id => $item) {
                            $rankedItems[$class][$item] = $items->where('item_id', $item)->first();
                        }
                    }
                }
                $rankedItems = collect($rankedItems)->map(function ($class) use ($items) {
                    return collect($class)->mapWithKeys(function ($item, $id) use ($items) {
                        $itemCache = $items->where('item_id', $id)->first();

                        return [$itemCache->gameItem->name => $itemCache];
                    })->sortByDesc('min_price_nq')->take(5);
                });

                // Update the list organized by node with price information
                $availableItems = $availableItems->map(function ($chunk) use ($items) {
                    return collect($chunk)->map(function ($node) use ($items) {
                        return $node->mapWithKeys(function ($item, $id) use ($items) {
                            $itemCache = $items->where('item_id', $item)->first();

                            return [$itemCache->gameItem->name => $itemCache];
                        });
                    });
                });
            } elseif ($isValid) {
                // Do nothing, and do not unset the selected world
            } else {
                // If the world name is invalid, unset it
                // so that the frontend treats it as not having selected anything
                $request->offsetUnset('world');
            }
        }

        return view('diadem.index', [
            'items'       => $availableItems ?? [],
            'rankedItems' => $rankedItems ?? [],
        ]);
    }
}
