<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LevelingController extends Controller {
    /**
     * Show the leveling calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLeveling(Request $request) {
        // Cap out EXP at whatever is appropriate for the level
        if ($request->has('character_level') && $request->has('character_exp')) {
            if ($request->get('character_exp') > config('ffxiv.leveling_data.level_data.level_exp.'.$request->get('character_level'))) {
                $request->merge(['character_exp' => config('ffxiv.leveling_data.level_data.level_exp.'.$request->get('character_level'))]);
            }
        }

        // Calculate EXP bonus
        $bonusBase = 0 +
            ($request->get('temp_food') ? 3 : 0) +
            ($request->get('temp_fc') ? $request->get('temp_fc') * 5 : 0) +
            ($request->get('override') ? $request->get('override') : 0);
        // Add level range specific bonuses
        $bonus = [
            1  => $bonusBase +
                ($request->get('character_highest') > $request->get('character_level') ? 100 : 0) +
                ($request->get('character_road') ? 100 : 0) +
                ($request->get('gear_brand_new') ? 30 : 0) +
                ($request->get('gear_earring') ? 30 : 0),
            31 => $bonusBase +
                ($request->get('character_highest') > $request->get('character_level') ? 100 : 0) +
                ($request->get('character_road') ? 100 : 0) +
                ($request->get('gear_earring') ? 30 : 0),
            (config('ffxiv.leveling_data.level_data.level_cap') - 9) => $bonusBase +
                ($request->get('character_highest') > $request->get('character_level') ? 50 : 0),
        ];

        for ($level = ($request->get('character_level') && $request->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? $request->get('character_level') : 1); $level < config('ffxiv.leveling_data.level_data.level_cap'); $level++) {
            // Calculate dungeon values
            if ($level >= 15) {
                // Determine the relevant EXP bonus value and convert it from percentage
                $dungeonBonus = ((int) collect($bonus)->filter(function ($value, $key) use ($level) {
                    return $key <= $level;
                })->last());
                $dungeonBonus = $dungeonBonus ? ($dungeonBonus / 100) : 1;

                // Determine the highest level "leveling" dungeon available
                $dungeonSearch = collect(config('ffxiv.leveling_data.dungeon.dungeon_data'))->filter(function ($value, $key) use ($level) {
                    return $key <= $level;
                })->take(-1);
                $dungeon[$level]['level'] = $dungeonSearch->keys()->last();

                if ($dungeon[$level]['level']) {
                    // If a dungeon was successfully located, calculate estimated EXP
                    // and from there, estimated runs to next level and excess EXP
                    $dungeon[$level]['exp'] = round((int) $dungeonSearch->last() * $dungeonBonus);
                    $dungeon[$level]['runs'] = ceil((config('ffxiv.leveling_data.level_data.level_exp.'.$level) - (request()->get('character_exp') ?? 0)) / $dungeon[$level]['exp']);
                    $dungeon[$level]['overage'] = (($dungeon[$level]['exp'] * $dungeon[$level]['runs']) - ((config('ffxiv.leveling_data.level_data.level_exp.'.$level) - (request()->get('character_exp') ?? 0)))) + ($dungeon[$level - 1]['overage'] ?? 0);
                    // The total runs counter resets at the start of a level range
                    // since the frontend displays ranges separately
                    $dungeon[$level]['total_runs'] = (isset($dungeon[$level - 1]['total_runs']) && !in_array($level, array_keys(config('ffxiv.leveling_data.level_data.level_ranges'))) ? $dungeon[$level - 1]['total_runs'] : 0) + $dungeon[$level]['runs'];
                }
            }

            // Calculate deep dungeon values
            switch ($level) {
                // What deep dungeon is relevant depends on level range
                // and there's no relevant deep dungeon for ShB
                case $level <= 60:
                    $deepDungeon[$level]['dungeon'] = 'PotD';
                    $deepDungeon[$level]['level'] = config('ffxiv.leveling_data.potd.label');
                    break;
                case $level >= 61 && $level <= 70:
                    $deepDungeon[$level]['dungeon'] = 'HoH';
                    $deepDungeon[$level]['level'] = config('ffxiv.leveling_data.hoh.label');
                    break;
                case $level >= 81 && $level <= 90:
                    $deepDungeon[$level]['dungeon'] = 'EO';
                    $deepDungeon[$level]['level'] = config('ffxiv.leveling_data.eo.label');
                    break;
            }

            if (isset($deepDungeon[$level]['dungeon'])) {
                // Look up the floor multiplier for the relevant deep dungeon
                switch ($deepDungeon[$level]['dungeon']) {
                    case 'PotD':
                        $floorMult = config('ffxiv.leveling_data.potd.floor_mult.51');
                        break;
                    case 'HoH':
                        $floorMult = config('ffxiv.leveling_data.hoh.floor_mult.21');
                        break;
                    case 'EO':
                        $floorMult = config('ffxiv.leveling_data.eo.floor_mult.21');
                        break;
                }

                // Deep dungeon EXP is impacted by only two bonuses: armoury and road
                $deepDungeonBonus = 1;
                if ($request->get('character_highest') > $request->get('character_level')) {
                    if ($level < 80) {
                        $deepDungeonBonus += 1;
                    } else {
                        $deepDungeonBonus += 0.5;
                    }
                }
                if ($request->get('character_road') && $level < 80) {
                    $deepDungeonBonus += 1;
                }
                // with a general formula of (level + modifier) * base EXP value
                // and then * floor multipler and any applicable EXP bonuses
                $deepDungeon[$level]['exp'] = round(($level + config('ffxiv.leveling_data.'.strtolower($deepDungeon[$level]['dungeon']).'.level_data.'.$level)[0]) * config('ffxiv.leveling_data.'.strtolower($deepDungeon[$level]['dungeon']).'.level_data.'.$level)[1] * $floorMult * $deepDungeonBonus);

                $deepDungeon[$level]['runs'] = ceil((config('ffxiv.leveling_data.level_data.level_exp.'.$level) - (request()->get('character_exp') ?? 0)) / $deepDungeon[$level]['exp']);
                $deepDungeon[$level]['overage'] = (($deepDungeon[$level]['exp'] * $deepDungeon[$level]['runs']) - ((config('ffxiv.leveling_data.level_data.level_exp.'.$level) - (request()->get('character_exp') ?? 0)))) + ($deepDungeon[$level - 1]['overage'] ?? 0);
                $deepDungeon[$level]['total_runs'] = (isset($deepDungeon[$level - 1]['total_runs']) && !in_array($level, array_keys(config('ffxiv.leveling_data.level_data.level_ranges'))) ? $deepDungeon[$level - 1]['total_runs'] : 0) + $deepDungeon[$level]['runs'];
            }

            // Calculate Frontline values
            if ($level >= 30) {
                // Frontline awards flat EXP unimpacted by any bonuses,
                // but varies depending on a win or loss, so average those values
                // Otherwise the EXP formula is similar to deep dungeons'
                $frontline[$level]['loss'] = ($level + config('ffxiv.leveling_data.frontline.level_data.'.$level)[0]) * config('ffxiv.leveling_data.frontline.level_data.'.$level)[1];
                $frontline[$level]['win'] = $frontline[$level]['loss'] * config('ffxiv.leveling_data.frontline.win_mult');
                $frontline[$level]['avg_exp'] = ($frontline[$level]['loss'] + $frontline[$level]['win']) / 2;

                $frontline[$level]['runs'] = ceil((config('ffxiv.leveling_data.level_data.level_exp.'.$level) - (request()->get('character_exp') ?? 0)) / $frontline[$level]['avg_exp']);
                $frontline[$level]['overage'] = (($frontline[$level]['avg_exp'] * $frontline[$level]['runs']) - ((config('ffxiv.leveling_data.level_data.level_exp.'.$level) - (request()->get('character_exp') ?? 0)))) + ($frontline[$level - 1]['overage'] ?? 0);
                $frontline[$level]['total_runs'] = (isset($frontline[$level - 1]['total_runs']) && !in_array($level, array_keys(config('ffxiv.leveling_data.level_data.level_ranges'))) ? $frontline[$level - 1]['total_runs'] : 0) + $frontline[$level]['runs'];
            }
        }

        return view('leveling.index', [
            'bonus'       => $bonus,
            'dungeon'     => $dungeon,
            'deepDungeon' => $deepDungeon,
            'frontline'   => $frontline,
        ]);
    }
}
