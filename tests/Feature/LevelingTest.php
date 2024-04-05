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
    public function testGetLeveling($charId, $charJob, $charLevel, $charExp, $charHighest, $charRoad, $gearRing, $gearEarring, $tempFood, $tempFc, $override): void {
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
        if ($tempFc) {
            $request = (isset($request) ? $request.'&' : '?').'temp_fc='.$tempFc;
        }
        if ($override) {
            $request = (isset($request) ? $request.'&' : '?').'override='.$override;
        }

        $response = $this->get('leveling'.($request ?? ''));

        if($charJob && $charJob == 15) {
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

            if ($charId && ($charJob && in_array($charJob, array_keys(config('ffxiv.classjob'))))) {
                $response->assertSeeText(config('ffxiv.classjob.'.$charJob));
            } elseif ($charJob || $charId) {
                $response->assertSeeText('Please enter both a character ID and valid combat class/job to retrieve information from The Lodestone.');
            }
        }
    }

    public static function levelingProvider() {
        return [
            'no data'              => [null, null, null, null, null, 0, 0, 0, 0, null, null],
            'only exp'             => [null, null, null, 500, null, 0, 0, 0, 0, null, null],
            'only highest'         => [null, null, null, null, 90, 0, 0, 0, 0, null, null],
            'only road'            => [null, null, null, null, null, 1, 0, 0, 0, null, null],
            'only ring'            => [null, null, null, null, null, 0, 1, 0, 0, null, null],
            'only earring'         => [null, null, null, null, null, 0, 0, 1, 0, null, null],
            'only food'            => [null, null, null, null, null, 0, 0, 0, 1, null, null],
            'only FC'              => [null, null, null, null, null, 0, 0, 0, 0, 2, null],
            'only override'        => [null, null, null, null, null, 0, 0, 0, 0, null, 10],
            'level 1'              => [null, null, 1, null, null, 0, 0, 0, 0, null, null],
            'level 1, exp'         => [null, null, 1, 200, null, 0, 0, 0, 0, null, null],
            'level 1, highest'     => [null, null, 1, null, 90, 0, 0, 0, 0, null, null],
            'level 1, road'        => [null, null, 1, null, null, 1, 0, 0, 0, null, null],
            'level 1, ring'        => [null, null, 1, null, null, 0, 1, 0, 0, null, null],
            'level 1, earring'     => [null, null, 1, null, null, 0, 0, 1, 0, null, null],
            'level 1, food'        => [null, null, 1, null, null, 0, 0, 0, 1, null, null],
            'level 1, FC'          => [null, null, 1, null, null, 0, 0, 0, 0, 2, null],
            'level 1, override'    => [null, null, 1, null, null, 0, 0, 0, 0, null, 10],
            'level 1, everything'  => [null, null, 1, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 51'             => [null, null, 51, null, null, 0, 0, 0, 0, null, null],
            'level 51, exp'        => [null, null, 51, 500, null, 0, 0, 0, 0, null, null],
            'level 51, highest'    => [null, null, 51, null, 90, 0, 0, 0, 0, null, null],
            'level 51, road'       => [null, null, 51, null, null, 1, 0, 0, 0, null, null],
            'level 51, ring'       => [null, null, 51, null, null, 0, 1, 0, 0, null, null],
            'level 51, earring'    => [null, null, 51, null, null, 0, 0, 1, 0, null, null],
            'level 51, food'       => [null, null, 51, null, null, 0, 0, 0, 1, null, null],
            'level 51, FC'         => [null, null, 51, null, null, 0, 0, 0, 0, 2, null],
            'level 51, override'   => [null, null, 51, null, null, 0, 0, 0, 0, null, 10],
            'level 51, everything' => [null, null, 51, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 61'             => [null, null, 61, null, null, 0, 0, 0, 0, null, null],
            'level 61, exp'        => [null, null, 61, 500, null, 0, 0, 0, 0, null, null],
            'level 61, highest'    => [null, null, 61, null, 90, 0, 0, 0, 0, null, null],
            'level 61, road'       => [null, null, 61, null, null, 1, 0, 0, 0, null, null],
            'level 61, ring'       => [null, null, 61, null, null, 0, 1, 0, 0, null, null],
            'level 61, earring'    => [null, null, 61, null, null, 0, 0, 1, 0, null, null],
            'level 61, food'       => [null, null, 61, null, null, 0, 0, 0, 1, null, null],
            'level 61, FC'         => [null, null, 61, null, null, 0, 0, 0, 0, 2, null],
            'level 61, override'   => [null, null, 61, null, null, 0, 0, 0, 0, null, 10],
            'level 61, everything' => [null, null, 61, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 71'             => [null, null, 71, null, null, 0, 0, 0, 0, null, null],
            'level 71, exp'        => [null, null, 71, 500, null, 0, 0, 0, 0, null, null],
            'level 71, highest'    => [null, null, 71, null, 90, 0, 0, 0, 0, null, null],
            'level 71, road'       => [null, null, 71, null, null, 1, 0, 0, 0, null, null],
            'level 71, ring'       => [null, null, 71, null, null, 0, 1, 0, 0, null, null],
            'level 71, earring'    => [null, null, 71, null, null, 0, 0, 1, 0, null, null],
            'level 71, food'       => [null, null, 71, null, null, 0, 0, 0, 1, null, null],
            'level 71, FC'         => [null, null, 71, null, null, 0, 0, 0, 0, 2, null],
            'level 71, override'   => [null, null, 71, null, null, 0, 0, 0, 0, null, 10],
            'level 71, everything' => [null, null, 71, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 81'             => [null, null, 81, null, null, 0, 0, 0, 0, null, null],
            'level 81, exp'        => [null, null, 81, 500, null, 0, 0, 0, 0, null, null],
            'level 81, highest'    => [null, null, 81, null, 90, 0, 0, 0, 0, null, null],
            'level 81, road'       => [null, null, 81, null, null, 1, 0, 0, 0, null, null],
            'level 81, ring'       => [null, null, 81, null, null, 0, 1, 0, 0, null, null],
            'level 81, earring'    => [null, null, 81, null, null, 0, 0, 1, 0, null, null],
            'level 81, food'       => [null, null, 81, null, null, 0, 0, 0, 1, null, null],
            'level 81, FC'         => [null, null, 81, null, null, 0, 0, 0, 0, 2, null],
            'level 81, override'   => [null, null, 81, null, null, 0, 0, 0, 0, null, 10],
            'level 81, everything' => [null, null, 81, 200, 90, 1, 1, 1, 1, 2, 10],
            'level 90'             => [null, null, 90, null, null, 0, 0, 0, 0, null, null],
            'level 90, exp'        => [null, null, 90, 500, null, 0, 0, 0, 0, null, null],
            'level 90, highest'    => [null, null, 90, null, 90, 0, 0, 0, 0, null, null],
            'level 90, road'       => [null, null, 90, null, null, 1, 0, 0, 0, null, null],
            'level 90, ring'       => [null, null, 90, null, null, 0, 1, 0, 0, null, null],
            'level 90, earring'    => [null, null, 90, null, null, 0, 0, 1, 0, null, null],
            'level 90, food'       => [null, null, 90, null, null, 0, 0, 0, 1, null, null],
            'level 90, FC'         => [null, null, 90, null, null, 0, 0, 0, 0, 2, null],
            'level 90, override'   => [null, null, 90, null, null, 0, 0, 0, 0, null, 10],
            'level 90, everything' => [null, null, 90, 200, 90, 1, 1, 1, 1, 2, 10],
        ];
    }

    public static function lodestoneDataProvider() {
        [
            'character ID, MRD/WAR'     => [33459349, 3, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, DRK'         => [33459349, 32, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, GNB'         => [33459349, 37, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, CNJ/WHM'     => [33459349, 6, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, ACN/SCH/SMN' => [33459349, 26, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, AST'         => [33459349, 33, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, SGE'         => [33459349, 40, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, PGL/MNK'     => [33459349, 2, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, LNC/DRG'     => [33459349, 4, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, ROG/NIN'     => [33459349, 29, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, SAM'         => [33459349, 34, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, RPR'         => [33459349, 39, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, ARC/BRD'     => [33459349, 5, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, MCH'         => [33459349, 31, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, DNC'         => [33459349, 38, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, THM/BLM'     => [33459349, 7, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, RDM'         => [33459349, 35, null, null, null, 0, 0, 0, 0, null, null],
        ];

        return [
            'only character ID'            => [33459349, null, null, null, null, 0, 0, 0, 0, null, null],
            'only job'                     => [null, 1, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, combat job'     => [33459349, 1, null, null, null, 0, 0, 0, 0, null, null],
            'character ID, non-combat job' => [33459349, 15, null, null, null, 0, 0, 0, 0, null, null],
        ];
    }
}
