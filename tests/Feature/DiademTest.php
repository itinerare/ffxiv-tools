<?php

namespace Tests\Feature;

use App\Jobs\CreateUniversalisRecords;
use App\Jobs\UpdateGameItem;
use App\Jobs\UpdateUniversalisCaches;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DiademTest extends TestCase {
    use RefreshDatabase;

    /**
     * Test getting the Diadem tool.
     *
     * @param string $world
     * @param bool   $initialized
     * @param bool   $expected
     */
    #[DataProvider('diademProvider')]
    public function testGetDiadem($world, $initialized, $expected): void {
        Queue::fake();

        if ($initialized) {
            $items = collect(config('ffxiv.diadem_items.node_data'))->flatten();

            // Initialize game item and Universalis records, echoing the chunking usually used to do so
            foreach ($items->chunk(100) as $chunk) {
                (new UpdateGameItem($chunk))->handle();
            }
            foreach ($items->chunk(100) as $chunk) {
                (new CreateUniversalisRecords($world, $chunk))->handle();
            }
        }

        $response = $this->get('diadem'.($world ? '?world='.$world : ''));

        $response->assertStatus(200);

        if ($expected && $initialized) {
            $response->assertSee('Showing Results for '.ucfirst($world));
            //Queue::assertPushed(UpdateUniversalisCaches::class);
        } elseif ($expected) {
            $response->assertSee('Item data for '.ucfirst($world));
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        } else {
            $response->assertSee('Please select a world!');
            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        }
    }

    public static function diademProvider() {
        return [
            'no world'                 => [null, 0, 0],
            'valid world'              => ['zalera', 0, 1],
            'valid world, initialized' => ['zalera', 1, 1],
            'invalid world'            => ['fake', 0, 0],
        ];
    }
}
