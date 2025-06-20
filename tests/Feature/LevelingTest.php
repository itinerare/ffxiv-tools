<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LevelingTest extends TestCase {
    /**
     * Test getting the Leveling tool.
     * Note that this only tests that it is accessible under various conditions,
     * not that its logic is sound/the numbers it's putting out make sense.
     *
     * @param int|null $charId
     * @param int|null $charJob
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
    #[DataProvider('lodestoneDataProvider')]
    public function testGetLeveling($charId, $charJob, $charLevel, $charExp, $charHighest, $charRoad, $gearRing, $gearEarring, $tempFood, $tempRested, $tempFc, $override): void {
        // Add any additional data to the request
        if ($charId || $charJob) {
            $request = (isset($request) ? $request.'&' : '?').'use_lodestone=1';
        } else {
            $request = (isset($request) ? $request.'&' : '?').'use_lodestone=0';
        }
        if ($charId) {
            $request = (isset($request) ? $request.'&' : '?').'character_id='.$charId;
        }
        if ($charJob) {
            $request = (isset($request) ? $request.'&' : '?').'character_job='.$charJob;
        }
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
        if ($tempRested) {
            $request = (isset($request) ? $request.'&' : '?').'temp_rested='.$tempRested;
        }
        if ($tempFc) {
            $request = (isset($request) ? $request.'&' : '?').'temp_fc='.$tempFc;
        }
        if ($override) {
            $request = (isset($request) ? $request.'&' : '?').'override='.$override;
        }

        $response = $this->get('leveling'.($request ?? ''));

        if ($charJob && $charJob == 15) {
            // In the case of a validation error, it is technically handled as a redirect
            $response->assertSessionHasErrors();
        } else {
            $response->assertStatus(200);
            $response->assertSessionHasNoErrors();

            // Test that level ranges are/aren't visible
            foreach (config('ffxiv.leveling_data.level_data.level_ranges') as $floor=>$range) {
                if (!$charLevel || ($charLevel <= $range['ceiling'] || $charLevel == config('ffxiv.leveling_data.level_data.level_cap'))) {
                    $response->assertSee($floor.' to '.$range['ceiling']);
                } elseif ($charLevel && $charLevel < $range['ceiling']) {
                    $response->assertDontSee($floor.' to '.$range['ceiling']);
                }
            }

            if ($charId && ($charJob && in_array($charJob, array_keys((array) config('ffxiv.classjob'))))) {
                $response->assertSeeText(config('ffxiv.classjob.'.$charJob));
            } elseif ($charJob || $charId) {
                $response->assertSeeText('Please enter both a character ID and valid combat class/job to retrieve information from The Lodestone.');
            }
        }
    }

    public static function levelingProvider() {
        return [
            'no data'               => [null, null, null, null, null, 0, 0, 0, 0, 0, null, null],
            'only exp'              => [null, null, null, 500, null, 0, 0, 0, 0, 0, null, null],
            'only highest'          => [null, null, null, null, 90, 0, 0, 0, 0, 0, null, null],
            'only road'             => [null, null, null, null, null, 1, 0, 0, 0, 0, null, null],
            'only ring'             => [null, null, null, null, null, 0, 1, 0, 0, 0, null, null],
            'only earring'          => [null, null, null, null, null, 0, 0, 1, 0, 0, null, null],
            'only food'             => [null, null, null, null, null, 0, 0, 0, 1, 0, null, null],
            'only FC'               => [null, null, null, null, null, 0, 0, 0, 0, 0, 2, null],
            'only rested'           => [null, null, null, null, null, 0, 0, 0, 0, 1, null, null],
            'only override'         => [null, null, null, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 1'               => [null, null, 1, null, null, 0, 0, 0, 0, 0, null, null],
            'level 1, exp'          => [null, null, 1, 200, null, 0, 0, 0, 0, 0, null, null],
            'level 1, highest'      => [null, null, 1, null, 90, 0, 0, 0, 0, 0, null, null],
            'level 1, road'         => [null, null, 1, null, null, 1, 0, 0, 0, 0, null, null],
            'level 1, ring'         => [null, null, 1, null, null, 0, 1, 0, 0, 0, null, null],
            'level 1, earring'      => [null, null, 1, null, null, 0, 0, 1, 0, 0, null, null],
            'level 1, food'         => [null, null, 1, null, null, 0, 0, 0, 1, 0, null, null],
            'level 1, FC'           => [null, null, 1, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 1, rested'       => [null, null, 1, null, null, 0, 0, 0, 0, 1, null, null],
            'level 1, override'     => [null, null, 1, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 1, everything'   => [null, null, 1, 200, 90, 1, 1, 1, 1, 1, 2, 10],
            'level 50'              => [null, null, 50, null, null, 0, 0, 0, 0, 0, null, null],
            'level 50, exp'         => [null, null, 50, 500, null, 0, 0, 0, 0, 0, null, null],
            'level 50, highest'     => [null, null, 50, null, 90, 0, 0, 0, 0, 0, null, null],
            'level 50, road'        => [null, null, 50, null, null, 1, 0, 0, 0, 0, null, null],
            'level 50, ring'        => [null, null, 50, null, null, 0, 1, 0, 0, 0, null, null],
            'level 50, earring'     => [null, null, 50, null, null, 0, 0, 1, 0, 0, null, null],
            'level 50, food'        => [null, null, 50, null, null, 0, 0, 0, 1, 0, null, null],
            'level 50, FC'          => [null, null, 50, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 50, rested'      => [null, null, 50, null, null, 0, 0, 0, 0, 1, null, null],
            'level 50, override'    => [null, null, 50, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 50, everything'  => [null, null, 50, 200, 90, 1, 1, 1, 1, 1, 2, 10],
            'level 60'              => [null, null, 60, null, null, 0, 0, 0, 0, 0, null, null],
            'level 60, exp'         => [null, null, 60, 500, null, 0, 0, 0, 0, 0, null, null],
            'level 60, highest'     => [null, null, 60, null, 90, 0, 0, 0, 0, 0, null, null],
            'level 60, road'        => [null, null, 60, null, null, 1, 0, 0, 0, 0, null, null],
            'level 60, ring'        => [null, null, 60, null, null, 0, 1, 0, 0, 0, null, null],
            'level 60, earring'     => [null, null, 60, null, null, 0, 0, 1, 0, 0, null, null],
            'level 60, food'        => [null, null, 60, null, null, 0, 0, 0, 1, 0, null, null],
            'level 60, FC'          => [null, null, 60, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 60, rested'      => [null, null, 60, null, null, 0, 0, 0, 0, 1, null, null],
            'level 60, override'    => [null, null, 60, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 60, everything'  => [null, null, 60, 200, 90, 1, 1, 1, 1, 1, 2, 10],
            'level 70'              => [null, null, 70, null, null, 0, 0, 0, 0, 0, null, null],
            'level 70, exp'         => [null, null, 70, 500, null, 0, 0, 0, 0, 0, null, null],
            'level 70, highest'     => [null, null, 70, null, 90, 0, 0, 0, 0, 0, null, null],
            'level 70, road'        => [null, null, 70, null, null, 1, 0, 0, 0, 0, null, null],
            'level 70, ring'        => [null, null, 70, null, null, 0, 1, 0, 0, 0, null, null],
            'level 70, earring'     => [null, null, 70, null, null, 0, 0, 1, 0, 0, null, null],
            'level 70, food'        => [null, null, 70, null, null, 0, 0, 0, 1, 0, null, null],
            'level 70, FC'          => [null, null, 70, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 70, rested'      => [null, null, 70, null, null, 0, 0, 0, 0, 1, null, null],
            'level 70, override'    => [null, null, 70, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 70, everything'  => [null, null, 70, 200, 90, 1, 1, 1, 1, 1, 2, 10],
            'level 80'              => [null, null, 80, null, null, 0, 0, 0, 0, 0, null, null],
            'level 80, exp'         => [null, null, 80, 500, null, 0, 0, 0, 0, 0, null, null],
            'level 80, highest'     => [null, null, 80, null, 90, 0, 0, 0, 0, 0, null, null],
            'level 80, road'        => [null, null, 80, null, null, 1, 0, 0, 0, 0, null, null],
            'level 80, ring'        => [null, null, 80, null, null, 0, 1, 0, 0, 0, null, null],
            'level 80, earring'     => [null, null, 80, null, null, 0, 0, 1, 0, 0, null, null],
            'level 80, food'        => [null, null, 80, null, null, 0, 0, 0, 1, 0, null, null],
            'level 80, FC'          => [null, null, 80, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 80, rested'      => [null, null, 80, null, null, 0, 0, 0, 0, 1, null, null],
            'level 80, override'    => [null, null, 80, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 80, everything'  => [null, null, 80, 200, 90, 1, 1, 1, 1, 1, 2, 10],
            'level 90'              => [null, null, 90, null, null, 0, 0, 0, 0, 0, null, null],
            'level 90, exp'         => [null, null, 90, 500, null, 0, 0, 0, 0, 0, null, null],
            'level 90, highest'     => [null, null, 90, null, 100, 0, 0, 0, 0, 0, null, null],
            'level 90, road'        => [null, null, 90, null, null, 1, 0, 0, 0, 0, null, null],
            'level 90, ring'        => [null, null, 90, null, null, 0, 1, 0, 0, 0, null, null],
            'level 90, earring'     => [null, null, 90, null, null, 0, 0, 1, 0, 0, null, null],
            'level 90, food'        => [null, null, 90, null, null, 0, 0, 0, 1, 0, null, null],
            'level 90, FC'          => [null, null, 90, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 90, rested'      => [null, null, 90, null, null, 0, 0, 0, 0, 1, null, null],
            'level 90, override'    => [null, null, 90, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 90, everything'  => [null, null, 90, 200, 100, 1, 1, 1, 1, 1, 2, 10],
            'level 100'             => [null, null, 100, null, null, 0, 0, 0, 0, 0, null, null],
            'level 100, exp'        => [null, null, 100, 500, null, 0, 0, 0, 0, 0, null, null],
            'level 100, highest'    => [null, null, 100, null, 100, 0, 0, 0, 0, 0, null, null],
            'level 100, road'       => [null, null, 100, null, null, 1, 0, 0, 0, 0, null, null],
            'level 100, ring'       => [null, null, 100, null, null, 0, 1, 0, 0, 0, null, null],
            'level 100, earring'    => [null, null, 100, null, null, 0, 0, 1, 0, 0, null, null],
            'level 100, food'       => [null, null, 100, null, null, 0, 0, 0, 1, 0, null, null],
            'level 100, FC'         => [null, null, 100, null, null, 0, 0, 0, 0, 0, 2, null],
            'level 100, rested'     => [null, null, 100, null, null, 0, 0, 0, 0, 1, null, null],
            'level 100, override'   => [null, null, 100, null, null, 0, 0, 0, 0, 0, null, 10],
            'level 100, everything' => [null, null, 100, 200, 100, 1, 1, 1, 1, 1, 2, 10],
        ];
    }

    public static function lodestoneDataProvider() {
        [
            'character ID, MRD/WAR'     => [33459349, 3, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, DRK'         => [33459349, 32, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, GNB'         => [33459349, 37, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, CNJ/WHM'     => [33459349, 6, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, ACN/SCH/SMN' => [33459349, 26, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, AST'         => [33459349, 33, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, SGE'         => [33459349, 40, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, PGL/MNK'     => [33459349, 2, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, LNC/DRG'     => [33459349, 4, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, ROG/NIN'     => [33459349, 29, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, SAM'         => [33459349, 34, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, RPR'         => [33459349, 39, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, ARC/BRD'     => [33459349, 5, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, MCH'         => [33459349, 31, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, DNC'         => [33459349, 38, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, THM/BLM'     => [33459349, 7, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, RDM'         => [33459349, 35, null, null, null, 0, 0, 0, 0, 0, null, null],
        ];

        return [
            'only character ID'            => [33459349, null, null, null, null, 0, 0, 0, 0, 0, null, null],
            'only job'                     => [null, 1, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, combat job'     => [33459349, 1, null, null, null, 0, 0, 0, 0, 0, null, null],
            'character ID, non-combat job' => [33459349, 15, null, null, null, 0, 0, 0, 0, 0, null, null],
        ];
    }
}
