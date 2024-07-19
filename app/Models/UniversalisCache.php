<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class UniversalisCache extends Model {
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id', 'world',
        'min_price_nq', 'min_price_hq',
        'nq_sale_velocity', 'hq_sale_velocity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'universalis_cache';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the game item associated with this record.
     */
    public function gameItem() {
        return $this->belongsTo(GameItem::class, 'item_id', 'item_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to include records only for a given world.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $world
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWorld($query, $world) {
        return $query->where('world', $world);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the updated date, formatted for display.
     *
     * @return string
     */
    public function getUpdatedTimeAttribute() {
        return '<abbr data-toggle="tooltip" title="'.$this->updated_at->format('F j Y, H:i:s').' '.strtoupper($this->updated_at->timezone->getAbbreviatedName($this->updated_at->isDST())).'">'.$this->updated_at->diffForHumans().'</abbr>';
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Creates an empty cache record.
     *
     * @param string $world
     * @param int    $id
     *
     * @return bool
     */
    public function recordItem($world, $id) {
        if (self::world($world)->where('item_id', $id)->exists()) {
            return true;
        }

        $gameItem = self::create([
            'item_id' => $id,
            'world'   => $world,
        ]);

        if (!$gameItem) {
            echo 'Failed to create game item.';

            return false;
        }

        return true;
    }

    /**
     * Updates stored data from Universalis.
     * Attempts to make as few requests as possible.
     *
     * @param string $world
     * @param mixed  $chunk
     *
     * @return \Illuminate\Support\Collection
     */
    public function updateCaches($world, $chunk) {
        // Filter down to only items that have not been updated recently, or without price data
        $items = self::world($world)->whereIn('item_id', $chunk)->where(function ($query) {
            $query->where('updated_at', '<', Carbon::now()->subHours(6))
                ->orWhereNull('min_price_nq');
        })->get();

        echo 'Updating '.$items->count().' items, starting at '.$items->first()->item_id.'...'."\n";

        // Only make a request to Universalis if there are items to update
        if ($items->count()) {
            // Format a comma-separated string of item IDs to make a request to Universalis
            $idString = implode(',', $items->pluck('item_id')->toArray());

            $response = Http::retry(3, 100, throw: false)->get('https://universalis.app/api/v2/'.$world.'/'.$idString.'?listings=1');

            if ($response->successful()) {
                echo 'Successful response...'."\n";
                // The response is then returned as JSON
                $response = json_decode($response->getBody(), true);
                // Affirm that the response is an array for safety
                if (is_array($response)) {
                    // Assemble a list of items with prices, ignoring any for which no price data exists
                    foreach ($items as $item) {
                        $item->update([
                            'min_price_nq'     => $response['items'][$item->item_id]['minPriceNQ'] ?? null,
                            'min_price_hq'     => $response['items'][$item->item_id]['minPriceHQ'] ?? null,
                            'nq_sale_velocity' => $response['items'][$item->item_id]['nqSaleVelocity'] ?? null,
                            'hq_sale_velocity' => $response['items'][$item->item_id]['hqSaleVelocity'] ?? null,
                        ]);
                    }
                }
            } else {
                echo 'Unsuccessful response...'."\n";
            }
        }

        return true;
    }
}
