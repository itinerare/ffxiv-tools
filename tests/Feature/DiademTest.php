<?php

namespace Tests\Feature;

use Tests\TestCase;

class DiademTest extends TestCase {
    /**
     * Test getting the Diadem tool.
     *
     * @dataProvider diademProvider
     *
     * @param string $world
     * @param bool   $expected
     */
    public function testGetDiadem($world, $expected): void {
        $response = $this->get('diadem'.($world ? '?world='.$world : ''));

        $response->assertStatus(200);

        if ($expected) {
            $response->assertSee('Showing Results for '.ucfirst($world));
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
