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

        // The following few arrays are organized by node contents
        $this->availableItems = [];
        $this->availableItems['BTN'] = [
            collect([
                31965 => "approved grade 4 skybuilders' mahogany log",
                31972 => "sprig of approved grade 4 skybuilders' mistletoe",
                31981 => "approved grade 4 skybuilders' white cedar log",
                31992 => "approved grade 4 artisanal skybuilders' raspberry",

                29881 => "lump of approved grade 2 artisanal skybuilders' hardened sap",
                31263 => "piece of approved grade 3 artisanal skybuilders' amber",
            ]),

            collect([
                31961 => "approved grade 4 skybuilders' switch",
                31971 => "approved grade 4 skybuilders' spruce log",
                31982 => "lump of approved grade 4 skybuilders' primordial resin",
                31991 => "approved grade 4 artisanal skybuilders' log",

                29880 => "approved grade 2 artisanal skybuilders' log",
                31262 => "approved grade 3 artisanal skybuilders' log",
            ]),

            collect([
                31966 => "tuft of approved grade 4 skybuilders' sesame",
                31974 => "approved grade 4 skybuilders' vine",
                31985 => "approved grade 4 skybuilders' tortoise",
                31994 => "approved grade 4 artisanal skybuilders' cocoon",

                29883 => "approved grade 2 artisanal skybuilders' cotton boll",
                31265 => "box of approved grade 3 artisanal skybuilders' rice",
            ]),

            collect([
                31967 => "approved grade 4 skybuilders' cotton boll",
                31975 => "basket of approved grade 4 skybuilders' tea leaves",
                31984 => "approved grade 4 skybuilders' gossamer cotton boll",
                31995 => "clump of approved grade 4 artisanal skybuilders' barbgrass",

                29884 => "approved grade 2 artisanal skybuilders' dawn lizard",
                31266 => "approved grade 3 artisanal skybuilders' vine",
            ]),

            collect([
                31962 => "tuft of approved grade 4 skybuilders' hemp",
                31973 => "approved grade 4 skybuilders' toad",
                31983 => "sheaf of approved grade 4 skybuilders' wheat",
                31993 => "approved grade 4 artisanal skybuilders' caiman",

                29882 => "sheaf of approved grade 2 artisanal skybuilders' wheat",
                31264 => "approved grade 3 artisanal skybuilders' cotton boll",
            ]),

            collect([
                32001 => "approved grade 4 skybuilders' umbral galewood branch",

                29890 => "approved grade 2 skybuilders' umbral galewood log",
                31272 => "pot of approved grade 3 skybuilders' umbral galewood sap",
            ]),

            collect([
                32002 => "clump of approved grade 4 skybuilders' umbral dirtleaf",

                29891 => "approved grade 2 skybuilders' umbral earthcap",
                31273 => "approved grade 3 skybuilders' umbral tortoise",
            ]),
        ];
        $this->availableItems['MIN'] = [
            collect([
                31963 => "chunk of approved grade 4 skybuilders' iron ore",
                31976 => "chunk of approved grade 4 skybuilders' electrum ore",
                31986 => "chunk of approved grade 4 skybuilders' gold ore",
                31996 => "chunk of approved grade 4 artisanal skybuilders' cloudstone",

                29885 => "chunk of approved grade 2 artisanal skybuilders' cloudstone",
                31267 => "chunk of approved grade 3 artisanal skybuilders' cloudstone",
            ]),

            collect([
                31968 => "chunk of approved grade 4 skybuilders' ore",
                31977 => "pinch of approved grade 4 skybuilders' alumen",
                31987 => "chunk of approved grade 4 skybuilders' finest rock salt",
                31997 => "bottle of approved grade 4 artisanal skybuilders' spring water",

                29886 => "chunk of approved grade 2 artisanal skybuilders' rock salt",
                31268 => "approved grade 3 artisanal skybuilders' basilisk egg",
            ]),

            collect([
                31970 => "pinch of approved grade 4 skybuilders' mythrite sand",
                31980 => "slab of approved grade 4 skybuilders' ragstone",
                31990 => "chunk of approved grade 4 skybuilders' bluespirit ore",
                31999 => "sack of approved grade 4 artisanal skybuilders' silex",

                29889 => "chunk of approved grade 2 artisanal skybuilders' jade",
                31271 => "chunk of approved grade 3 artisanal skybuilders' granite",
            ]),

            collect([
                31964 => "pinch of approved grade 4 skybuilders' iron sand",
                31979 => "pinch of approved grade 4 skybuilders' gold sand",
                31989 => "pinch of approved grade 4 skybuilders' mineral sand",
                32000 => "chunk of approved grade 4 artisanal skybuilders' prismstone",

                29888 => "pinch of approved grade 2 artisanal skybuilders' aurum regis sand",
                31270 => "sack of approved grade 3 artisanal skybuilders' clay",
            ]),

            collect([
                31969 => "chunk of approved grade 4 skybuilders' rock salt",
                31978 => "bottle of approved grade 4 skybuilders' spring water",
                31988 => "bottle of approved grade 4 skybuilders' truespring water",
                31998 => "approved grade 4 artisanal skybuilders' ice stalagmite",

                29887 => "bottle of approved grade 2 artisanal skybuilders' spring water",
                31269 => "pinch of approved grade 3 artisanal skybuilders' alumen",
            ]),

            collect([
                32003 => "chunk of approved grade 4 skybuilders' umbral flarerock",

                29892 => "approved grade 2 skybuilders' umbral flarestone",
                31274 => "approved grade 3 skybuilders' umbral magma shard",
            ]),

            collect([
                32004 => "pinch of approved grade 4 skybuilders' umbral levinsand",

                29893 => "approved grade 2 skybuilders' umbral levinshard",
                31275 => "approved grade 3 skybuilders' umbral levinite",
            ]),
        ];

        $this->availableItems = collect($this->availableItems);

        $this->dataCenters = [
            'Aether' => [
                'Adamantoise', 'Cactuar', 'Faerie', 'Gilgamesh', 'Jenova', 'Midgardsormr', 'Sargatanas', 'Siren',
            ],
            'Primal' => [
                'Behemoth', 'Excalibur', 'Exodus', 'Famfrit', 'Hyperion', 'Lamia', 'Leviathan', 'Ultros',
            ],
            'Crystal' => [
                'Balmung', 'Brynhildr', 'Coeurl', 'Diabolos', 'Goblin', 'Malboro', 'Mateus', 'Zalera',
            ],
            'Dynamis' => [
                'Halicarnassus', 'Maduin', 'Marilith', 'Seraph',
            ],
        ];
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
            'dataCenters' => $this->dataCenters,
            'world'       => $request->get('world') ?? null,
            'items'       => $this->availableItems,
            'rankedItems' => $rankedItems ?? null,
        ]);
    }
}
