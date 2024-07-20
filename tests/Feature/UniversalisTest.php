<?php

namespace Tests\Feature;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\UpdateGameItem;
use App\Jobs\UpdateUniversalisCacheChunk;
use App\Jobs\UpdateUnivsersalisCaches;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class UniversalisTest extends TestCase {
    use RefreshDatabase;

    protected function setUp(): void {
        parent::setUp();

        Queue::fake();
        $this->items = collect(config('ffxiv.diadem_items.node_data'))->flatten();
    }

    /**
     * Tests game item record creation.
     */
    public function testGameItemCreation(): void {
        // Process the job with a very small chunk for efficiency
        $chunk = $this->items->chunk(3)->last();
        (new UpdateGameItem($chunk))->handle();

        foreach ($chunk as $item) {
            $this->assertDatabaseHas('game_items', [
                'item_id' => $item,
            ]);
            // Sideways-check that the name of the item has been filled by checking that it's not null
            $this->assertDatabaseMissing('game_items', [
                'item_id' => $item,
                'name'    => null,
            ]);
        }
    }

    /**
     * Tests the main Universalis update job queueing "chunk" jobs.
     */
    public function testDispatchUniversalisUpdate(): void {
        $job = new UpdateUnivsersalisCaches('zalera', $this->items);
        $job->handle();

        // Universalis accepts up to 100 item IDs per request,
        // so the overarching update job chunks the list of items and creates an update job per chunk
        Queue::assertPushed(UpdateUniversalisCacheChunk::class, (int) ceil($this->items->count() / 100));
    }

    /**
     * Tests "chunk" job handling.
     */
    public function testUniversalisChunkUpdate(): void {
        // Usually, the overarching per-world update job handles chunking so here it needs to be done manually
        // This also selects the last/smallest chunk for ease of running tests
        $chunk = $this->items->chunk(100)->last();

        // Formulate a response body in line with Universalis' response formatting
        $responseBody = [];
        foreach ($chunk as $item) {
            $responseBody['items'][$item] = [
                'nqSaleVelocity' => 0,
                'hqSaleVelocity' => 0,
                'minPriceNQ'     => mt_rand(1, 2000),
                'minPriceHQ'     => mt_rand(1, 2000),
                'lastUploadTime' => '1721492174306',
            ];
        }

        // Fake responses from Universalis to reduce load/improve reliability
        Http::fake(['universalis.app/*' => Http::response($responseBody)]);

        // Set up records for the world and chunk so there's somewhere to put the data retrieved
        (new CreateUniversalisRecords('zalera', $chunk))->handle();

        // Check that the appropriate number of empty records has been created
        foreach ($chunk as $item) {
            $this->assertDatabaseHas('universalis_cache', [
                'item_id'          => $item,
                'min_price_nq'     => null,
                'min_price_hq'     => null,
                'last_upload_time' => null,
            ]);
        }

        // Handle the update job itself
        (new UpdateUniversalisCacheChunk('zalera', $chunk))->handle();

        // Assert that records have been updated with the data from the fake response body
        foreach ($chunk as $item) {
            $this->assertDatabaseHas('universalis_cache', [
                'item_id'          => $item,
                'min_price_nq'     => $responseBody['items'][$item]['minPriceNQ'],
                'min_price_hq'     => $responseBody['items'][$item]['minPriceHQ'],
                'last_upload_time' => Carbon::createFromTimestampMs($responseBody['items'][$item]['lastUploadTime']),
            ]);
        }
    }
}
