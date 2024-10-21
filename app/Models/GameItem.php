<?php

namespace App\Models;

use App\Http\Controllers\CraftingController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Record items by ID, retrieving data from Teamcraft's dumps.
     *
     * @param \Illuminate\Support\Collection $chunk
     *
     * @return bool
     */
    public function recordItem($chunk) {
        $requestHelper = new CraftingController;

        // Fetch Teamcraft's item, monster drop, and gathering data
        $itemData = $requestHelper->teamcraftDataRequest('items.json');
        if ($itemData->successful()) {
            $itemData = json_decode($itemData->getBody(), true);
            if (is_array($itemData)) {
                // Format the response for easy access to item info
                $itemData = collect($itemData)->mapWithKeys(function ($value, $key) {
                    return [$key => $value['en']];
                });
            } else {
                unset($itemData);
            }
        }

        $dropData = $requestHelper->teamcraftDataRequest('drop-sources.json');
        if ($dropData->successful()) {
            $dropData = json_decode($dropData->getBody(), true);
            if (!is_array($dropData)) {
                unset($dropData);
            }
        }

        $gatheringData = $requestHelper->teamcraftDataRequest('gathering-items.json');
        if ($gatheringData->successful()) {
            $gatheringData = json_decode($gatheringData->getBody(), true);
            if (is_array($gatheringData)) {
                $gatheringData = collect($gatheringData);
            } else {
                unset($gatheringData);
            }
        }

        $shopData = $requestHelper->teamcraftDataRequest('shops.json');
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

        foreach ($chunk as $item) {
            if (self::where('item_id', $item)->exists()) {
                if (self::where('item_id', $item)->whereNull('name')->exists()) {
                    self::where('item_id', $item)->update([
                        'name'        => $itemData[$item] ?? null,
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
                    'name'        => $itemData[$item] ?? null,
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

        return true;
    }
}
