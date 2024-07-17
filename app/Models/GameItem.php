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

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Record an item by ID, fetching its name from XIVAPI.
     *
     * @param int $id
     *
     * @return bool
     */
    public function recordItem($id) {
        if (self::where('item_id', $id)->whereNotNull('name')->exists()) {
            return true;
        } elseif (self::where('item_id', $id)->whereNull('name')->exists()) {
            $gameItem = self::where('item_id', $id)->whereNull('name')->first();
        }

        // Make a request to XIVAPI for the item name
        $response = Http::retry(3, 100, throw: false)->get('https://xivapi.com/item/'.$id);

        if ($response->successful()) {
            // The response is then returned as JSON
            $response = json_decode($response->getBody(), true);
            // Affirm that the response is an array for safety
            if (is_array($response) && isset($response['Name'])) {
                $name = $response['Name'];
            }

            // Clear the response after processing it
            unset($response);
        } else {
            flash('A request to XIVAPI failed; please try again later.')->error();
        }

        if (isset($gameItem) && $gameItem) {
            $gameItem->update([
                'name' => $name ?? null,
            ]);
        } else {
            $gameItem = self::create([
                'item_id' => $id,
                'name'    => $name ?? null,
            ]);

            if (!$gameItem) {
                flash('Failed to create game item.')->error();

                return false;
            }
        }

        return true;
    }
}
