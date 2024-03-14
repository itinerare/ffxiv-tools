<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LevelingTest extends TestCase {
    /**
     * Test getting the Leveling tool.
     * Note that this only tests that it is accessible under various conditions,
     * not that its logic is sound/the numbers it's putting out make sense.
     *
     * @param int|null $charLevel
     * @param int|null $charExp
     * @param int|null $charHighest
     * @param bool     $charRoad
     * @param bool     $gearRing
     * @param bool     $gearEarring
     * @param bool     $tempFood
     * @param int|null $tempFc
     * @param int|null $override
     */
    #[DataProvider('levelingProvider')]
    public function testGetLeveling($charLevel, $charExp, $charHighest, $charRoad, $gearRing, $gearEarring, $tempFood, $tempFc, $override): void {
        // Add any additional data to the request
        if ($charLevel) {
            $request = (isset($request) ? $request.'&' : '?').'character_level='.$charLevel;
        }
        if ($charExp) {
            $request = (isset($request) ? $request.'&' : '?').'character_exp='.$charExp;
        }
        if ($charHighest) {
            $request = (isset($request) ? $request.'&' : '?').'character_highest='.$charHighest;
        }
        if ($charRoad) {
            $request = (isset($request) ? $request.'&' : '?').'character_road='.$charRoad;
        }
        if ($gearRing) {
            $request = (isset($request) ? $request.'&' : '?').'gear_brand_new='.$gearRing;
        }
        if ($gearEarring) {
            $request = (isset($request) ? $request.'&' : '?').'gear_earring='.$gearEarring;
        }
        if ($tempFood) {
            $request = (isset($request) ? $request.'&' : '?').'temp_food='.$tempFood;
        }
        if ($tempFc) {
            $request = (isset($request) ? $request.'&' : '?').'temp_fc='.$tempFc;
        }
        if ($override) {
            $request = (isset($request) ? $request.'&' : '?').'override='.$override;
        }

        $response = $this->get('leveling'.($request ?? ''));
        $response->assertStatus(200);

        // Test that level ranges are/aren't visible
        foreach (config('ffxiv.leveling_data.level_data.level_ranges') as $floor=>$range) {
            if (!$charLevel || ($charLevel <= $range['ceiling'] || $charLevel == config('ffxiv.leveling_data.level_data.level_cap'))) {
                $response->assertSee($floor.' to '.$range['ceiling']);
            } elseif ($charLevel && $charLevel < $range['ceiling']) {
                $response->assertDontSee($floor.' to '.$range['ceiling']);
            }
        }
    }

    public static function levelingProvider() {
        return [
            'no data'              => [null, null, null, 0, 0, 0, 0, null, null],
            'only exp'             => [null, 500, null, 0, 0, 0, 0, null, null],
            'only highest'         => [null, null, 90, 0, 0, 0, 0, null, null],
            'only road'            => [null, null, null, 1, 0, 0, 0, null, null],
            'only ring'            => [null, null, null, 0, 1, 0, 0, null, null],
            'only earring'         => [null, null, null, 0, 0, 1, 0, null, null],
            'only food'            => [null, null, null, 0, 0, 0, 1, null, null],
            'only FC'              => [null, null, null, 0, 0, 0, 0, 2, null],
            'only override'        => [null, null, null, 0, 0, 0, 0, null, 10],
            'level 1'              => [1, null, null, 0, 0, 0, 0, null, null],
            'level 1, exp'         => [1, 200, null, 0, 0, 0, 0, null, null],
            'level 1, highest'     => [1, null, 90, 0, 0, 0, 0, null, null],
            'level 1, road'        => [1, null, null, 1, 0, 0, 0, null, null],
            'level 1, ring'        => [1, null, null, 0, 1, 0, 0, null, null],
            'level 1, earring'     => [1, null, null, 0, 0, 1, 0, null, null],
            'level 1, food'        => [1, null, null, 0, 0, 0, 1, null, null],
            'level 1, FC'          => [1, null, null, 0, 0, 0, 0, 2, null],
            'level 1, override'    => [1, null, null, 0, 0, 0, 0, null, 10],
            'level 1, everything'  => [1, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 51'             => [51, null, null, 0, 0, 0, 0, null, null],
            'level 51, exp'        => [51, 500, null, 0, 0, 0, 0, null, null],
            'level 51, highest'    => [51, null, 90, 0, 0, 0, 0, null, null],
            'level 51, road'       => [51, null, null, 1, 0, 0, 0, null, null],
            'level 51, ring'       => [51, null, null, 0, 1, 0, 0, null, null],
            'level 51, earring'    => [51, null, null, 0, 0, 1, 0, null, null],
            'level 51, food'       => [51, null, null, 0, 0, 0, 1, null, null],
            'level 51, FC'         => [51, null, null, 0, 0, 0, 0, 2, null],
            'level 51, override'   => [51, null, null, 0, 0, 0, 0, null, 10],
            'level 51, everything' => [51, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 61'             => [61, null, null, 0, 0, 0, 0, null, null],
            'level 61, exp'        => [61, 500, null, 0, 0, 0, 0, null, null],
            'level 61, highest'    => [61, null, 90, 0, 0, 0, 0, null, null],
            'level 61, road'       => [61, null, null, 1, 0, 0, 0, null, null],
            'level 61, ring'       => [61, null, null, 0, 1, 0, 0, null, null],
            'level 61, earring'    => [61, null, null, 0, 0, 1, 0, null, null],
            'level 61, food'       => [61, null, null, 0, 0, 0, 1, null, null],
            'level 61, FC'         => [61, null, null, 0, 0, 0, 0, 2, null],
            'level 61, override'   => [61, null, null, 0, 0, 0, 0, null, 10],
            'level 61, everything' => [61, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 71'             => [71, null, null, 0, 0, 0, 0, null, null],
            'level 71, exp'        => [71, 500, null, 0, 0, 0, 0, null, null],
            'level 71, highest'    => [71, null, 90, 0, 0, 0, 0, null, null],
            'level 71, road'       => [71, null, null, 1, 0, 0, 0, null, null],
            'level 71, ring'       => [71, null, null, 0, 1, 0, 0, null, null],
            'level 71, earring'    => [71, null, null, 0, 0, 1, 0, null, null],
            'level 71, food'       => [71, null, null, 0, 0, 0, 1, null, null],
            'level 71, FC'         => [71, null, null, 0, 0, 0, 0, 2, null],
            'level 71, override'   => [71, null, null, 0, 0, 0, 0, null, 10],
            'level 71, everything' => [71, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 81'             => [81, null, null, 0, 0, 0, 0, null, null],
            'level 81, exp'        => [81, 500, null, 0, 0, 0, 0, null, null],
            'level 81, highest'    => [81, null, 90, 0, 0, 0, 0, null, null],
            'level 81, road'       => [81, null, null, 1, 0, 0, 0, null, null],
            'level 81, ring'       => [81, null, null, 0, 1, 0, 0, null, null],
            'level 81, earring'    => [81, null, null, 0, 0, 1, 0, null, null],
            'level 81, food'       => [81, null, null, 0, 0, 0, 1, null, null],
            'level 81, FC'         => [81, null, null, 0, 0, 0, 0, 2, null],
            'level 81, override'   => [81, null, null, 0, 0, 0, 0, null, 10],
            'level 81, everything' => [81, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 90'             => [90, null, null, 0, 0, 0, 0, null, null],
            'level 90, exp'        => [90, 500, null, 0, 0, 0, 0, null, null],
            'level 90, highest'    => [90, null, 90, 0, 0, 0, 0, null, null],
            'level 90, road'       => [90, null, null, 1, 0, 0, 0, null, null],
            'level 90, ring'       => [90, null, null, 0, 1, 0, 0, null, null],
            'level 90, earring'    => [90, null, null, 0, 0, 1, 0, null, null],
            'level 90, food'       => [90, null, null, 0, 0, 0, 1, null, null],
            'level 90, FC'         => [90, null, null, 0, 0, 0, 0, 2, null],
            'level 90, override'   => [90, null, null, 0, 0, 0, 0, null, 10],
            'level 90, everything' => [90, 200, 90, 1, 1, 1, 1, 2, 10],
        ];
    }
}
