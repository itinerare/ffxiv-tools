<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response = $this->get('diadem'.($world ? '?world='.$world : ''));

        $response->assertStatus(200);

        if ($expected) {
            $response->assertSee('Item data for '.ucfirst($world));
        } else {
            $response->assertSee('Please select a world!');
        }
    }

    public static function diademProvider() {
        return [
            'no world'      => [null, 0],
            'valid world'   => ['zalera', 1],
            'invalid world' => ['fake', 0],
        ];
    }
}
