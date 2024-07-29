<?php

namespace Tests\Feature;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\UpdateGameItem;
use App\Jobs\UpdateUniversalisCaches;
use App\Models\GameRecipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CraftingTest extends TestCase {
    use RefreshDatabase;

    /**
     * Tests recipe retrieval.
     */
    public function testRetrieveRecipes(): void {
        Queue::fake();

        // Retrieve and process CUL recipes
        (new GameRecipe)->retrieveRecipes(15);

        // Just check that a specific recipe has been recorded as expected for now
        $this->assertDatabaseHas('game_recipes', [
            'recipe_id' => 5620,
            'item_id'   => 44087,
            'job'       => 15,
        ]);

        // Check that the relevant jobs have been pushed
        Queue::assertPushed(UpdateGameItem::class);
        Queue::assertPushed(CreateUniversalisRecords::class);
    }

    /**
     * Test getting the crafting profit calculator.
     *
     * @param string   $world
     * @param int|null $job
     * @param bool     $expected
     */
    #[DataProvider('craftingProfitProvider')]
    public function testGetCraftingCalc($world, $job, $expected): void {
        Queue::fake();

        if ($job) {
            // Fake requests to XIVAPI to save time/requests
            Http::fake(['xivapi.com/*' => Http::response(['Results' => []])]);

            // Initialize recipe, game item, and Universalis records, echoing the chunking usually used to do so
            (new GameRecipe)->retrieveRecipes($job);

            $items = GameRecipe::where('job', $job)->pluck('item_id');

            foreach ($items->chunk(100) as $chunk) {
                (new UpdateGameItem($chunk))->handle();
            }
            foreach ($items->chunk(100) as $chunk) {
                (new CreateUniversalisRecords($world, $chunk))->handle();
            }
        }

        $response = $this->get('crafting'.($world ? '?world='.$world.($job ? '&character_job='.$job : '') : ''));

        if ($expected && $job) {
            $response->assertStatus(200);
            $response->assertSessionHasNoErrors();

            $response->assertSee('Showing '.config('ffxiv.crafting.jobs')[$job].' results for '.ucfirst($world));

            Queue::assertPushed(UpdateUniversalisCaches::class);
        } elseif (!$expected && $job) {
            $response->assertSessionHasErrors();
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        } elseif ($expected) {
            $response->assertStatus(200);
            $response->assertSee('Settings');
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        } else {
            $response->assertStatus(200);

            $response->assertSee('Please select a world!');
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        }
    }

    public static function craftingProfitProvider() {
        return [
            'no world'                 => [null, 0, 0],
            'valid world'              => ['zalera', 0, 1],
            'valid world, valid job'   => ['zalera', 15, 1],
            'valid world, invalid job' => ['zalera', 16, 0],
            'invalid world'            => ['fake', 0, 0],
        ];
    }
}
