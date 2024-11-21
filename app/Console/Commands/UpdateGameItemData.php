<?php

namespace App\Console\Commands;

use App\Http\Controllers\CraftingController;
use App\Models\GameItem;
use Illuminate\Console\Command;

class UpdateGameItemData extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-game-item-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates extended game item data.';

    /**
     * Execute the console command.
     */
    public function handle(): int {
        $gameItems = GameItem::all();

        $this->info('Processing '.$gameItems->count().' item'.($gameItems->count() != 1 ? 's' : '').'...');
        if ($gameItems->count()) {
            $requestHelper = (new CraftingController);

            // Fetch Teamcraft's monster drop data, gathering (including fish) data, and shop data
            $dropData = $requestHelper->teamcraftDataRequest('drop-sources.json');
            if ($dropData->successful()) {
                $dropData = json_decode($dropData->getBody(), true);
            }
            $gatheringData = $requestHelper->teamcraftDataRequest('gathering-items.json');
            if ($gatheringData->successful()) {
                $gatheringData = (array) json_decode($gatheringData->getBody(), true);
                $gatheringData = collect($gatheringData);
            }
            $fishData = $requestHelper->teamcraftDataRequest('fish-parameter.json');
            if ($fishData->successful()) {
                $fishData = json_decode($fishData->getBody(), true);
            }
            $shopData = $requestHelper->teamcraftDataRequest('shops.json');
            if ($shopData->successful()) {
                $shopData = (array) json_decode($shopData->getBody(), true);

                $shopItems = collect($shopData)->transform(function ($shop) {
                    return collect((array) $shop['trades'])->transform(function ($trade) {
                        return collect((array) $trade['items'])->mapWithKeys(function (array $item) use ($trade) {
                            return [$item['id'] => $trade['currencies'][0]];
                        });
                    });
                })->flatten(1)->mapWithKeys(function ($trade) {
                    return $trade;
                });
            }

            $progressBar = $this->output->createProgressBar($gameItems->count());
            $progressBar->start();

            // Update game items
            foreach ($gameItems as $gameItem) {
                $gameItem->update([
                    'is_mob_drop' => in_array($gameItem->item_id, array_keys($dropData)),
                    'gather_data' => $gatheringData->where('itemId', $gameItem->item_id)->first() ? [
                        'stars'          => $gatheringData->where('itemId', $gameItem->item_id)->first()['stars'],
                        'perception_req' => $gatheringData->where('itemId', $gameItem->item_id)->first()['perceptionReq'],
                        'is_fish'        => false,
                    ] : (isset($fishData[$gameItem->item_id]) ? [
                        'stars'    => $fishData[$gameItem->item_id]['stars'] ?? 0,
                        'folklore' => $fishData[$gameItem->item_id]['folklore'] ?? null,
                        'is_fish'  => true,
                    ] : null),
                    'shop_data' => isset($shopItems[$gameItem->item_id]) ? [
                        'currency' => $shopItems[$gameItem->item_id]['id'] ?? null,
                        'cost'     => $shopItems[$gameItem->item_id]['amount'] ?? null,
                    ] : null,
                ]);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->line("\n".'Done!');
        } else {
            $this->line('No items to process!');
        }

        return 0;
    }
}
