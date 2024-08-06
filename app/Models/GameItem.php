<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class GameItem extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id', 'name', 'gather_data', 'is_mob_drop', 'shop_data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'game_items';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gather_data' => 'array',
        'shop_data'   => 'array',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the item name, or, failing that, the ID.
     *
     * @return string
     */
    public function getNameAttribute() {
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        return 'Item ID '.$this->item_id;
    }

    /**
     * Get URL of the item's Universalis page.
     *
     * @return string
     */
    public function getUniversalisUrlAttribute() {
        return 'https://universalis.app/market/'.$this->item_id;
    }

    /**
     * Get URL of the item's Teamcraft page.
     *
     * @return string
     */
    public function getTeamcraftUrlAttribute() {
        return 'https://ffxivteamcraft.com/db/en/item/'.$this->item_id;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Record items by ID, fetching names from XIVAPI.
     *
     * @param \Illuminate\Support\Collection $chunk
     *
     * @return bool
     */
    public function recordItem($chunk) {
        // Format a comma-separated string of item IDs to make a request to XIVAPI
        $idString = implode(',', $chunk->toArray());

        $response = Http::retry(3, 100, throw: false)->get('https://xivapi.com/item?ids='.$idString);

        // Fetch Teamcraft's monster drop data and gathering data
        $dropData = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/staging/libs/data/src/lib/json/drop-sources.json');
        if ($dropData->successful()) {
            $dropData = json_decode($dropData->getBody(), true);
            if (!is_array($dropData)) {
                unset($dropData);
            }
        }
        $gatheringData = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/staging/libs/data/src/lib/json/gathering-items.json');
        if ($gatheringData->successful()) {
            $gatheringData = json_decode($gatheringData->getBody(), true);
            if (is_array($gatheringData)) {
                $gatheringData = collect($gatheringData);
            } else {
                unset($gatheringData);
            }
        }
        $shopData = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/72867c936b7d46a52c176d374b0969d6f24e3877/libs/data/src/lib/json/shops.json');
        if ($shopData->successful()) {
            $shopData = json_decode($shopData->getBody(), true);

            $shopItems = collect($shopData)->transform(function ($shop) {
                return collect($shop['trades'])->transform(function ($trade) {
                    return collect($trade['items'])->mapWithKeys(function ($item) use ($trade) {
                        return [$item['id'] => $trade['currencies'][0]];
                    });
                });
            })->flatten(1)->mapWithKeys(function ($trade) {
                return $trade;
            });
        }

        // Start processing the XIVAPI response
        if ($response->successful()) {
            // The response is then returned as JSON
            $response = json_decode($response->getBody(), true);

            // Affirm that the response is an array for safety
            if (is_array($response)) {
                // Format the response for easy access to item info
                $response = collect($response['Results'])->mapWithKeys(function ($value, $key) {
                    return [$value['ID'] => $value];
                });

                foreach ($chunk as $item) {
                    if (self::where('item_id', $item)->exists()) {
                        if (self::where('item_id', $item)->whereNull('name')->exists()) {
                            self::where('item_id', $item)->update([
                                'name'        => $response[$item]['Name'] ?? null,
                                'is_mob_drop' => in_array($item, array_keys($dropData ?? [])),
                                'gather_data' => $gatheringData->where('itemId', $item)->first() ? [
                                    'stars'         => $gatheringData->where('itemId', $item)->first()['stars'] ?? null,
                                    'perceptionReq' => $gatheringData->where('itemId', $item)->first()['perceptionReq'] ?? null,
                                ] : null,
                                'shop_data' => isset($shopItems[$item]) ? [
                                    'currency' => $shopItems[$item]['id'] ?? null,
                                    'cost'     => $shopItems[$item]['amount'] ?? null,
                                ] : null,
                            ]);
                        }
                    } else {
                        self::create([
                            'item_id'     => $item,
                            'name'        => $response[$item]['Name'] ?? null,
                            'is_mob_drop' => in_array($item, array_keys($dropData ?? [])),
                            'gather_data' => $gatheringData->where('itemId', $item)->first() ? [
                                'stars'         => $gatheringData->where('itemId', $item)->first()['stars'] ?? null,
                                'perceptionReq' => $gatheringData->where('itemId', $item)->first()['perceptionReq'] ?? null,
                            ] : null,
                            'shop_data' => isset($shopItems[$item]) ? [
                                'currency' => $shopItems[$item]['id'] ?? null,
                                'cost'     => $shopItems[$item]['amount'] ?? null,
                            ] : null,
                        ]);
                    }
                }
            }
        } else {
            foreach ($chunk as $item) {
                if (!self::where('item_id', $item)->exists()) {
                    self::create([
                        'item_id' => $item,
                        'name'    => null,
                    ]);
                }
            }
        }

        return true;
    }
}
