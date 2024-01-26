<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class DiademController extends Controller {
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->items = config('ffxiv.diadem_items.items');
        $this->items = collect($this->items)->chunk(100);

        // Collect individual node data
        $this->availableItems = [];
        foreach (config('ffxiv.diadem_items.node_data.BTN') as $node) {
            $this->availableItems['BTN'][] = collect($node);
        }
        foreach (config('ffxiv.diadem_items.node_data.MIN') as $node) {
            $this->availableItems['MIN'][] = collect($node);
        }
        $this->availableItems = collect($this->availableItems);
    }

    /**
     * Show the diadem page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDiadem(Request $request) {
        if ($request->get('world')) {
            foreach ($this->items as $chunk) {
                // Format a comma-separated string of item IDs to make a request to Universalis
                $idString = implode(',', array_keys($chunk->toArray()));

                // Make a request for the lowest listing for each item
                $client = new Client();
                $response = $client->request('GET', 'https://universalis.app/api/v2/'.($request->get('world') ?? null).'/'.$idString.'?listings=1');

                // The response is then returned as JSON
                $response = json_decode($response->getBody(), true);

                // Assemble a list of items with prices, ignoring any for which no price data exists
                foreach ($chunk as $id=>$item) {
                    if (isset($response['items'][$id]['listings'][0]['pricePerUnit'])) {
                        $priceList[$id] = $response['items'][$id]['listings'][0]['pricePerUnit'] ?? null;
                    }
                }
            }

            // Assemble a list of available items ranked by price for each class
            // This provides a very simple overview
            foreach ($this->availableItems as $class=>$chunk) {
                foreach ($chunk as $node) {
                    foreach ($node as $id=>$item) {
                        $rankedItems[$class][$item] = $priceList[$id] ?? 'Unknown';
                    }
                }
            }
            arsort($rankedItems['BTN']);
            $rankedItems['BTN'] = collect($rankedItems['BTN']);
            arsort($rankedItems['MIN']);
            $rankedItems['MIN'] = collect($rankedItems['MIN']);

            // Update the list organized by node with price information
            $this->availableItems = $this->availableItems->map(function ($chunk) use ($priceList) {
                return collect($chunk)->map(function ($node) use ($priceList) {
                    return $node->mapWithKeys(function ($item, $id) use ($priceList) {
                        return [$item => $priceList[$id] ?? 'Unknown'];
                    });
                });
            });
        }

        return view('diadem', [
            'dataCenters' => config('ffxiv.data_centers'),
            'world'       => $request->get('world') ?? null,
            'items'       => $this->availableItems,
            'rankedItems' => $rankedItems ?? null,
        ]);
    }
}
