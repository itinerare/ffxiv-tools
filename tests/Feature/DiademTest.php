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
     * @param bool   $withCookie
     * @param bool   $expected
     * @param int    $status
     */
    #[DataProvider('diademProvider')]
    public function testGetDiadem($world, $initialized, $withCookie, $expected, $status): void {
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

        $response = $this->withCookies($withCookie ? [
            'diademSettings' => json_encode(['world' => $world]),
        ] : [])->get('diadem'.($world && !$withCookie ? '?world='.$world : ''));

        $response->assertStatus($status);

        if ($expected && $initialized) {
            $response->assertSee('Showing Results for '.ucfirst($world));
            $response->assertSessionHasNoErrors();

            //Queue::assertPushed(UpdateUniversalisCaches::class);
            $response->assertCookie('diademSettings', json_encode(['world' => $world]));
        } elseif ($expected) {
            $response->assertSee('Item data for '.ucfirst($world));
            $response->assertSessionHasNoErrors();

            Queue::assertNotPushed(UpdateUniversalisCaches::class);
            $response->assertCookie('diademSettings', json_encode(['world' => $world]));
        } else {
            if ($world) {
                $response->assertSessionHasErrors();
            } else {
                $response->assertSee('Please select a world!');
            }

            Queue::assertNotPushed(UpdateUniversalisCaches::class);
        }
    }

    public static function diademProvider() {
        return [
            'no world'                              => [null, 0, 0, 0, 200],
            'valid world'                           => ['zalera', 0, 0, 1, 200],
            'valid world, with cookie'              => ['zalera', 0, 1, 1, 200],
            'valid world, initialized'              => ['zalera', 1, 0, 1, 200],
            'valid world, initialized, with cookie' => ['zalera', 1, 1, 1, 200],
            'invalid world'                         => ['fake', 0, 0, 0, 302],
        ];
    }
}
