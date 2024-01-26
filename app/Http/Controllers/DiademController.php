<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class DiademController extends Controller {
    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->items = [29840 => "approved grade 2 skybuilders' switch", 29841 => "tuft of approved grade 2 skybuilders' hemp", 29842 => "chunk of approved grade 2 skybuilders' ore", 29843 => "pinch of approved grade 2 skybuilders' copper sand", 29844 => "approved grade 2 skybuilders' maple log", 29845 => "pot of approved grade 2 skybuilders' maple sap", 29846 => "clump of approved grade 2 skybuilders' flax", 29847 => "chunk of approved grade 2 skybuilders' zinc ore", 29848 => "chunk of approved grade 2 skybuilders' rock salt", 29849 => "pinch of approved grade 2 skybuilders' iron sand", 29850 => "approved grade 2 skybuilders' teak log", 29851 => "sprig of approved grade 2 skybuilders' mistletoe", 29852 => "approved grade 2 skybuilders' beehive", 29853 => "bundle of approved grade 2 skybuilders' straw", 29854 => "basket of approved grade 2 skybuilders' tea leaves", 29855 => "chunk of approved grade 2 skybuilders' cobalt ore", 29856 => "lump of approved grade 2 skybuilders' pigment", 29857 => "pot of approved grade 2 skybuilders' asphaltum", 29858 => "pinch of approved grade 2 skybuilders' mythril sand", 29859 => "chunk of approved grade 2 skybuilders' mica", 29860 => "approved grade 2 skybuilders' oak log", 29861 => "approved grade 2 skybuilders' feather", 29862 => "approved grade 2 skybuilders' vine", 29863 => "approved grade 2 skybuilders' toad", 29864 => "clump of approved grade 2 skybuilders' barbgrass", 29865 => "chunk of approved grade 2 skybuilders' mythrite ore", 29866 => "bottle of approved grade 2 skybuilders' spring water", 29867 => "pinch of approved grade 2 skybuilders' alumen", 29868 => "approved grade 2 skybuilders' rock", 29869 => "sack of approved grade 2 skybuilders' silex", 29870 => "approved grade 2 skybuilders' walnut log", 29871 => "lump of approved grade 2 skybuilders' resin", 29872 => "sheaf of approved grade 2 skybuilders' wheat", 29873 => "approved grade 2 skybuilders' cotton boll", 29874 => "approved grade 2 skybuilders' adder", 29875 => "chunk of approved grade 2 skybuilders' darksteel ore", 29876 => "chunk of approved grade 2 skybuilders' crystal-clear rock salt", 29877 => "bottle of approved grade 2 skybuilders' cloud drop water", 29878 => "pinch of approved grade 2 skybuilders' lutinite", 29879 => "chunk of approved grade 2 skybuilders' basalt", 29880 => "approved grade 2 artisanal skybuilders' log", 29881 => "lump of approved grade 2 artisanal skybuilders' hardened sap", 29882 => "sheaf of approved grade 2 artisanal skybuilders' wheat", 29883 => "approved grade 2 artisanal skybuilders' cotton boll", 29884 => "approved grade 2 artisanal skybuilders' dawn lizard", 29885 => "chunk of approved grade 2 artisanal skybuilders' cloudstone", 29886 => "chunk of approved grade 2 artisanal skybuilders' rock salt", 29887 => "bottle of approved grade 2 artisanal skybuilders' spring water", 29888 => "pinch of approved grade 2 artisanal skybuilders' aurum regis sand", 29889 => "chunk of approved grade 2 artisanal skybuilders' jade", 29890 => "approved grade 2 skybuilders' umbral galewood log", 29891 => "approved grade 2 skybuilders' umbral earthcap", 29892 => "approved grade 2 skybuilders' umbral flarestone", 29893 => "approved grade 2 skybuilders' umbral levinshard", 29992 => "set of grade 2 expert skybuilders' practice materials", 30014 => "approved grade 2 skybuilders' cloudskipper", 30015 => "approved grade 2 skybuilders' meditator", 30016 => "approved grade 2 skybuilders' coeurlfish", 30017 => "approved grade 2 skybuilders' garpike", 30018 => "approved grade 2 skybuilders' pirarucu", 30019 => "approved grade 2 skybuilders' brown bolo", 30020 => "approved grade 2 skybuilders' bitterling", 30021 => "approved grade 2 skybuilders' caiman", 30022 => "approved grade 2 skybuilders' cloud cutter", 30023 => "approved grade 2 skybuilders' vampiric tapestry", 30024 => "approved grade 2 skybuilders' tupuxuara", 30025 => "approved grade 2 skybuilders' blind manta", 30026 => "approved grade 2 artisanal skybuilders' rhomaleosaurus", 30027 => "approved grade 2 artisanal skybuilders' gobbie mask", 30028 => "approved grade 2 artisanal skybuilders' pterodactyl", 30029 => "approved grade 2 artisanal skybuilders' skyfish", 30030 => "approved grade 2 artisanal skybuilders' cometfish", 30031 => "approved grade 2 artisanal skybuilders' anomalocaris", 30032 => "approved grade 2 artisanal skybuilders' rhamphorhynchus", 30033 => "approved grade 2 artisanal skybuilders' dragon's soul", 31232 => "approved grade 3 skybuilders' switch", 31233 => "tuft of approved grade 3 skybuilders' hemp", 31234 => "chunk of approved grade 3 skybuilders' ore", 31235 => "pinch of approved grade 3 skybuilders' bomb ash", 31236 => "approved grade 3 skybuilders' ebony log", 31237 => "tuft of approved grade 3 skybuilders' sesame", 31238 => "approved grade 3 skybuilders' cotton boll", 31239 => "chunk of approved grade 3 skybuilders' titanium ore", 31240 => "chunk of approved grade 3 skybuilders' rock salt", 31241 => "handful of approved grade 3 skybuilders' hardsilver sand", 31242 => "approved grade 3 skybuilders' lauan log", 31243 => "sprig of approved grade 3 skybuilders' mistletoe", 31244 => "approved grade 3 skybuilders' toad", 31245 => "approved grade 3 skybuilders' vine", 31246 => "basket of approved grade 3 skybuilders' tea leaves", 31247 => "chunk of approved grade 3 skybuilders' Diadem iron ore", 31248 => "pinch of approved grade 3 skybuilders' alumen", 31249 => "bottle of approved grade 3 skybuilders' spring water", 31250 => "handful of approved grade 3 skybuilders' Diadem iron sand", 31251 => "slab of approved grade 3 skybuilders' siltstone", 31252 => "approved grade 3 skybuilders' cedar log", 31253 => "lump of approved grade 3 skybuilders' resin", 31254 => "sheaf of approved grade 3 skybuilders' wheat", 31255 => "approved grade 3 skybuilders' gossamer cotton boll", 31256 => "approved grade 3 skybuilders' adder", 31257 => "chunk of approved grade 3 skybuilders' aurum regis ore", 31258 => "chunk of approved grade 3 skybuilders' finest rock salt", 31259 => "bottle of approved grade 3 skybuilders' truespring water", 31260 => "pinch of approved grade 3 skybuilders' fossil dust", 31261 => "slab of approved grade 3 skybuilders' hard mudstone", 31262 => "approved grade 3 artisanal skybuilders' log", 31263 => "piece of approved grade 3 artisanal skybuilders' amber", 31264 => "approved grade 3 artisanal skybuilders' cotton boll", 31265 => "box of approved grade 3 artisanal skybuilders' rice", 31266 => "approved grade 3 artisanal skybuilders' vine", 31267 => "chunk of approved grade 3 artisanal skybuilders' cloudstone", 31268 => "approved grade 3 artisanal skybuilders' basilisk egg", 31269 => "pinch of approved grade 3 artisanal skybuilders' alumen", 31270 => "sack of approved grade 3 artisanal skybuilders' clay", 31271 => "chunk of approved grade 3 artisanal skybuilders' granite", 31272 => "pot of approved grade 3 skybuilders' umbral galewood sap", 31273 => "approved grade 3 skybuilders' umbral tortoise", 31274 => "approved grade 3 skybuilders' umbral magma shard", 31275 => "approved grade 3 skybuilders' umbral levinite", 31604 => "approved grade 3 skybuilders' thunderbolt sculpin", 31605 => "approved grade 3 skybuilders' alligator snapping turtle", 31606 => "approved grade 3 skybuilders' bonytongue", 31607 => "approved grade 3 skybuilders' hermit goby", 31608 => "approved grade 3 skybuilders' mudskipper", 31609 => "approved grade 3 skybuilders' steppe bullfrog", 31610 => "approved grade 3 skybuilders' golden loach", 31611 => "approved grade 3 skybuilders' bass", 31612 => "approved grade 3 skybuilders' cherax", 31613 => "approved grade 3 skybuilders' marimo", 31614 => "approved grade 3 skybuilders' catfish", 31615 => "approved grade 3 skybuilders' ricefish", 31616 => "approved grade 3 skybuilders' scorpionfly", 31617 => "approved grade 3 skybuilders' whiteloom", 31618 => "approved grade 3 skybuilders' pteranodon", 31619 => "approved grade 3 skybuilders' king's mantle", 31620 => "approved grade 3 skybuilders' blue medusa", 31621 => "approved grade 3 skybuilders' gurnard", 31622 => "approved grade 3 artisanal skybuilders' oscar", 31623 => "approved grade 3 artisanal skybuilders' blind manta", 31624 => "approved grade 3 artisanal skybuilders' mosasaur", 31625 => "approved grade 3 artisanal skybuilders' storm chaser", 31626 => "approved grade 3 artisanal skybuilders' archaeopteryx", 31627 => "approved grade 3 artisanal skybuilders' wyvern", 31628 => "approved grade 3 artisanal skybuilders' cloudshark", 31629 => "approved grade 3 artisanal skybuilders' helicoprion", 31961 => "approved grade 4 skybuilders' switch", 31962 => "tuft of approved grade 4 skybuilders' hemp", 31963 => "chunk of approved grade 4 skybuilders' iron ore", 31964 => "pinch of approved grade 4 skybuilders' iron sand", 31965 => "approved grade 4 skybuilders' mahogany log", 31966 => "tuft of approved grade 4 skybuilders' sesame", 31967 => "approved grade 4 skybuilders' cotton boll", 31968 => "chunk of approved grade 4 skybuilders' ore", 31969 => "chunk of approved grade 4 skybuilders' rock salt", 31970 => "pinch of approved grade 4 skybuilders' mythrite sand", 31971 => "approved grade 4 skybuilders' spruce log", 31972 => "sprig of approved grade 4 skybuilders' mistletoe", 31973 => "approved grade 4 skybuilders' toad", 31974 => "approved grade 4 skybuilders' vine", 31975 => "basket of approved grade 4 skybuilders' tea leaves", 31976 => "chunk of approved grade 4 skybuilders' electrum ore", 31977 => "pinch of approved grade 4 skybuilders' alumen", 31978 => "bottle of approved grade 4 skybuilders' spring water", 31979 => "pinch of approved grade 4 skybuilders' gold sand", 31980 => "slab of approved grade 4 skybuilders' ragstone", 31981 => "approved grade 4 skybuilders' white cedar log", 31982 => "lump of approved grade 4 skybuilders' primordial resin", 31983 => "sheaf of approved grade 4 skybuilders' wheat", 31984 => "approved grade 4 skybuilders' gossamer cotton boll", 31985 => "approved grade 4 skybuilders' tortoise", 31986 => "chunk of approved grade 4 skybuilders' gold ore", 31987 => "chunk of approved grade 4 skybuilders' finest rock salt", 31988 => "bottle of approved grade 4 skybuilders' truespring water", 31989 => "pinch of approved grade 4 skybuilders' mineral sand", 31990 => "chunk of approved grade 4 skybuilders' bluespirit ore", 31991 => "approved grade 4 artisanal skybuilders' log", 31992 => "approved grade 4 artisanal skybuilders' raspberry", 31993 => "approved grade 4 artisanal skybuilders' caiman", 31994 => "approved grade 4 artisanal skybuilders' cocoon", 31995 => "clump of approved grade 4 artisanal skybuilders' barbgrass", 31996 => "chunk of approved grade 4 artisanal skybuilders' cloudstone", 31997 => "bottle of approved grade 4 artisanal skybuilders' spring water", 31998 => "approved grade 4 artisanal skybuilders' ice stalagmite", 31999 => "sack of approved grade 4 artisanal skybuilders' silex", 32000 => "chunk of approved grade 4 artisanal skybuilders' prismstone", 32001 => "approved grade 4 skybuilders' umbral galewood branch", 32002 => "clump of approved grade 4 skybuilders' umbral dirtleaf", 32003 => "chunk of approved grade 4 skybuilders' umbral flarerock", 32004 => "pinch of approved grade 4 skybuilders' umbral levinsand", 32908 => "approved grade 4 skybuilders' zagas khaal", 32909 => "approved grade 4 skybuilders' goldsmith crab", 32910 => "approved grade 4 skybuilders' common bitterling", 32911 => "approved grade 4 skybuilders' skyloach", 32912 => "approved grade 4 skybuilders' glacier core", 32913 => "approved grade 4 skybuilders' kissing fish", 32914 => "approved grade 4 skybuilders' cavalry catfish", 32915 => "approved grade 4 skybuilders' manasail", 32916 => "approved grade 4 skybuilders' starflower", 32917 => "approved grade 4 skybuilders' cyan crab", 32918 => "approved grade 4 skybuilders' fickle krait", 32919 => "approved grade 4 skybuilders' proto-hropken", 32920 => "approved grade 4 skybuilders' ghost faerie", 32921 => "approved grade 4 skybuilders' ashfish", 32922 => "approved grade 4 skybuilders' whitehorse", 32923 => "approved grade 4 skybuilders' ocean cloud", 32924 => "approved grade 4 skybuilders' black fanfish", 32925 => "approved grade 4 skybuilders' sunfish", 32926 => "approved grade 4 artisanal skybuilders' sweatfish", 32927 => "approved grade 4 artisanal skybuilders' sculptor", 32928 => "approved grade 4 artisanal skybuilders' little Thalaos", 32929 => "approved grade 4 artisanal skybuilders' lightning chaser", 32930 => "approved grade 4 artisanal skybuilders' marrella", 32931 => "approved grade 4 artisanal skybuilders' crimson namitaro", 32932 => "approved grade 4 artisanal skybuilders' griffin", 32933 => "approved grade 4 artisanal skybuilders' meganeura"];
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
