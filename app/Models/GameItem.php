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
        'item_id', 'name',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'game_items';

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

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Record an items by ID, fetching names from XIVAPI.
     *
     * @param \Illuminate\Support\Collection $chunk
     *
     * @return bool
     */
    public function recordItem($chunk) {
        // Format a comma-separated string of item IDs to make a request to XIVAPI
        $idString = implode(',', $chunk->toArray());

        $response = Http::retry(3, 100, throw: false)->get('https://xivapi.com/item?ids='.$idString);

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
                                'name' => $response[$item]['Name'] ?? null,
                            ]);
                        }
                    } else {
                        self::create([
                            'item_id' => $item,
                            'name'    => $response[$item]['Name'] ?? null,
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
