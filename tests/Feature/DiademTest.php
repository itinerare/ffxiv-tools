<?php

namespace Tests\Feature;

use App\Jobs\UpdateUnivsersalisCaches;
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
     * @param bool   $expected
     */
    #[DataProvider('diademProvider')]
    public function testGetDiadem($world, $expected): void {
        Queue::fake();

        $response = $this->get('diadem'.($world ? '?world='.$world : ''));

        $response->assertStatus(200);

        if ($expected) {
            $response->assertSee('Item data for '.ucfirst($world));
        } else {
            $response->assertSee('Please select a world!');
        }

        Queue::assertNotPushed(UpdateUnivsersalisCaches::class);
    }

    public static function diademProvider() {
        return [
            'no world'      => [null, 0],
            'valid world'   => ['zalera', 1],
            'invalid world' => ['fake', 0],
        ];
    }
}
