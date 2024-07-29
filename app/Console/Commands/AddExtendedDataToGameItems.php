<?php

namespace App\Console\Commands;

use App\Models\GameItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AddExtendedDataToGameItems extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-extended-data-to-game-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() {
        // Filter down to game items that do not have any extended flag set
        $gameItems = GameItem::where('is_mob_drop', 0)->whereNull('gather_data');

        $this->info('Processing '.$gameItems->count().' item'.($gameItems->count() != 1 ? 's' : '').'...');
        if ($gameItems->count()) {
            // Fetch Teamcraft's monster drop data and gathering data
            $dropData = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/staging/libs/data/src/lib/json/drop-sources.json');
            if ($dropData->successful()) {
                $dropData = json_decode($dropData->getBody(), true);
            }
            $gatheringData = Http::retry(3, 100, throw: false)->get('https://raw.githubusercontent.com/ffxiv-teamcraft/ffxiv-teamcraft/staging/libs/data/src/lib/json/gathering-items.json');
            if ($gatheringData->successful()) {
                $gatheringData = json_decode($gatheringData->getBody(), true);
                $gatheringData = collect($gatheringData);
            }

            $progressBar = $this->output->createProgressBar($gameItems->count());
            $progressBar->start();

            // Update game items
            foreach ($gameItems->get() as $gameItem) {
                $gameItem->update([
                    'is_mob_drop' => in_array($gameItem->item_id, array_keys($dropData)),
                    'gather_data' => $gatheringData->where('itemId', $gameItem->item_id)->first() ? [
                        'stars'         => $gatheringData->where('itemId', $gameItem->item_id)->first()['stars'],
                        'perceptionReq' => $gatheringData->where('itemId', $gameItem->item_id)->first()['perceptionReq'],
                    ] : null,
                ]);
                $progressBar->advance();
            }

            $progressBar->finish();
            $this->line("\n".'Done!');
        } else {
            $this->line('No items to process!');
        }
    }
}
