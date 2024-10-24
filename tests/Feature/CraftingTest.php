<?php

namespace Tests\Feature;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\UpdateGameItem;
use App\Jobs\UpdateUniversalisCaches;
use App\Models\GameRecipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * @param bool     $withCookie
     * @param bool     $expected
     * @param int      $status
     */
    #[DataProvider('craftingProfitProvider')]
    public function testGetCraftingCalc($world, $job, $withCookie, $expected, $status): void {
        Queue::fake();

        if ($job) {
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

        $response = $this->withCookies($withCookie ? [
            'craftingSettings' => json_encode(['world' => $world, 'character_job' => $job]),
        ] : [])->get('crafting'.($world && !$withCookie ? '?world='.$world.($job ? '&character_job='.$job : '') : ''));

        $response->assertStatus($status);

        if ($expected && $job) {
            $response->assertSessionHasNoErrors();
            $response->assertSee('Showing '.config('ffxiv.crafting.jobs')[$job].' results for '.ucfirst($world));

            //Queue::assertPushed(UpdateUniversalisCaches::class);
        } elseif (!$expected && $job) {
            $response->assertSessionHasErrors();
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        } elseif ($expected) {
            $response->assertSee('Settings');
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        } else {
            if ($world) {
                $response->assertSessionHasErrors();
                $response->assertCookieMissing('craftingSettings');
            } else {
                $response->assertSee('Please select a world!');
            }

            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        }
    }

    public static function craftingProfitProvider() {
        return [
            'no world'                            => [null, 0, 0, 0, 200],
            'valid world'                         => ['zalera', 0, 0, 1, 200],
            'valid world, with cookie'            => ['zalera', 0, 1, 1, 200],
            'valid world, valid job'              => ['zalera', 15, 0, 1, 200],
            'valid world, valid job, with cookie' => ['zalera', 15, 1, 1, 200],
            'valid world, invalid job'            => ['zalera', 16, 0, 0, 302],
            'invalid world'                       => ['fake', 0, 0, 0, 302],
        ];
    }

    /**
     * Test getting the gathering profit calculator.
     *
     * @param string $world
     * @param bool   $withCookie
     * @param bool   $expected
     * @param int    $status
     */
    #[DataProvider('gatheringProfitProvider')]
    public function testGetGatheringCalc($world, $withCookie, $expected, $status): void {
        Queue::fake();

        if ($world) {
            // Initialize recipe, game item, and Universalis records, echoing the chunking usually used to do so
            (new GameRecipe)->retrieveRecipes(15);

            $items = GameRecipe::where('job', 15)->pluck('item_id');

            foreach ($items->chunk(100) as $chunk) {
                (new UpdateGameItem($chunk))->handle();
            }
            foreach ($items->chunk(100) as $chunk) {
                (new CreateUniversalisRecords($world, $chunk))->handle();
            }
        }

        $response = $this->withCookies($withCookie ? [
            'gatheringSettings' => json_encode(['world' => $world]),
        ] : [])->get('gathering'.($world && !$withCookie ? '?world='.$world : ''));

        $response->assertStatus($status);

        if ($expected) {
            $response->assertSessionHasNoErrors();
            $response->assertSee('Showing results for '.ucfirst($world));

            $response->assertCookie('gatheringSettings', json_encode(['world' => $world]));
            //Queue::assertPushed(UpdateUniversalisCaches::class);
        } else {
            if ($world) {
                $response->assertSessionHasErrors();
                $response->assertCookieMissing('gatheringSettings');
            } else {
                $response->assertSee('Please select a world!');
            }

            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        }
    }

    public static function gatheringProfitProvider() {
        return [
            'no world'                 => [null, 0, 0, 200],
            'valid world'              => ['zalera', 0, 1, 200],
            'valid world, with cookie' => ['zalera', 1, 1, 200],
            'invalid world'            => ['fake', 0, 0, 302],
        ];
    }
}
