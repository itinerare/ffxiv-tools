<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

            if ($isValid) {
                $items = collect(config('ffxiv.diadem_items.items'))->chunk(100);

                foreach ($items as $chunk) {
                    // Format a comma-separated string of item IDs to make a request to Universalis
                    $idString = implode(',', array_keys($chunk->toArray()));

                    $response = Http::retry(3, 100, throw: false)->get('https://universalis.app/api/v2/'.($request->get('world') ?? null).'/'.$idString.'?listings=1');

                    if ($response->successful()) {
                        // The response is then returned as JSON
                        $response = json_decode($response->getBody(), true);
                        // Affirm that the response is an array for safety
                        if (is_array($response)) {
                            // Assemble a list of items with prices, ignoring any for which no price data exists
                            foreach ($chunk as $id=>$item) {
                                if (isset($response['items'][$id]['listings'][0]['pricePerUnit'])) {
                                    $priceList[$id] = $response['items'][$id]['listings'][0]['pricePerUnit'] ?? null;
                                }
                            }
                        }

                        // Clear the response after processing it
                        unset($response);
                    } else {
                        flash('A request to Universalis failed; please try again later.')->error();
                    }
                }

                if (isset($priceList)) {
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
                    foreach ($availableItems as $class=>$chunk) {
                        foreach ($chunk as $node) {
                            foreach ($node as $id=>$item) {
                                $rankedItems[$class][$item] = $priceList[$id] ?? 0;
                            }
                        }
                    }
                    arsort($rankedItems['BTN']);
                    $rankedItems['BTN'] = collect($rankedItems['BTN']);
                    arsort($rankedItems['MIN']);
                    $rankedItems['MIN'] = collect($rankedItems['MIN']);

                    // Update the list organized by node with price information
                    $availableItems = $availableItems->map(function ($chunk) use ($priceList) {
                        return collect($chunk)->map(function ($node) use ($priceList) {
                            return $node->mapWithKeys(function ($item, $id) use ($priceList) {
                                return [$item => $priceList[$id] ?? 'Unknown'];
                            });
                        });
                    });
                }
            } else {
                // If the world name is invalid, unset it
                // so that the frontend treats it as not having selected anything
                $request->offsetUnset('world');
            }
        }

        return view('diadem.index', [
            'dataCenters' => config('ffxiv.data_centers'),
            'world'       => $request->get('world') ?? null,
            'items'       => $availableItems ?? [],
            'rankedItems' => $rankedItems ?? [],
        ]);
    }
}
