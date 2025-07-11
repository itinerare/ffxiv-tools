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
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id', 'world',
        'min_price_nq', 'min_price_hq', 'nq_sale_velocity', 'hq_sale_velocity',
        'last_upload_time',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'universalis_cache';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_upload_time' => 'datetime',
    ];

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

    /**
     * Scope a query to include records that need updating.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNeedsUpdate($query) {
        return $query->where(function ($query) {
            $query->where('updated_at', '<', Carbon::now()->subMinutes(config('ffxiv.universalis.cache_lifetime')))
                ->orWhereNull('min_price_nq');
        });
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

    /**
     * Get the time of last update on Universalis, formatted for display.
     *
     * @return string|null
     */
    public function getUploadTimeAttribute() {
        if (!isset($this->attributes['last_upload_time'])) {
            return null;
        }

        return '<abbr data-toggle="tooltip" title="'.$this->last_upload_time->format('F j Y, H:i:s').' '.strtoupper($this->last_upload_time->timezone->getAbbreviatedName($this->last_upload_time->isDST())).'">'.$this->last_upload_time->diffForHumans().'</abbr>';
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

        return true;
    }

    /**
     * Updates stored data from Universalis.
     *
     * @param string                         $world
     * @param \Illuminate\Support\Collection $items
     *
     * @return bool
     */
    public function updateCaches($world, $items) {
        // Format a comma-separated string of item IDs to make a request to Universalis
        $idString = implode(',', $items->pluck('item_id')->toArray());

        $response = Http::retry(3, 100, throw: false)->get('https://universalis.app/api/v2/'.$world.'/'.$idString.'?fields=items.lastUploadTime,items.minPriceNQ%2Citems.minPriceHQ%2Citems.nqSaleVelocity%2Citems.hqSaleVelocity');

        if ($response->successful()) {
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
                        'last_upload_time' => isset($response['items'][$item->item_id]['lastUploadTime']) ? Carbon::createFromTimestampMs($response['items'][$item->item_id]['lastUploadTime']) : null,
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Performs basic filtering to support recommendations.
     *
     * @param bool $hq
     *
     * @return bool
     */
    public function filterRecommendations($hq = false) {
        if ($this->hq_sale_velocity == 0 && $this->nq_sale_velocity == 0) {
            return false;
        }
        if ($this->last_upload_time < Carbon::now()->subHours(config('ffxiv.universalis.data_lifetime'))) {
            return false;
        }

        return true;
    }

    /**
     * Calculate relative weight of the item, used for making recommendations.
     *
     * @param bool       $hq
     * @param array|null $profit
     *
     * @return float
     */
    public function calculateWeight($hq = false, $profit = null) {
        $weight = 1;
        $priceWeight = $profit ? 7500 : 5000;
        $velocityWeight = 100;

        if ($hq) {
            if ($profit) {
                $weight += (($profit['hq'] ?? 0) / $priceWeight);
            } else {
                // This shouldn't come up (no more HQ gathering), but better safe than sorry
                $weight += ($this->min_price_hq / $priceWeight);
            }
            $weight = ($this->hq_sale_velocity / $velocityWeight) * $weight;
        } else {
            if ($profit) {
                $weight += (($profit['nq'] ?? 0) / $priceWeight);
            } else {
                $weight += ($this->min_price_nq / $priceWeight);
            }
            $weight = ($this->nq_sale_velocity / $velocityWeight) * $weight;
        }

        $weight -= ($this->last_upload_time->diffInHours(Carbon::now()) / 1000);

        return $weight;
    }
}
