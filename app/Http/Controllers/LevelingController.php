<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Lodestone\Parser\ParseCharacterClassJobs;

class LevelingController extends Controller {
    /**
     * Show the leveling calculator page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLeveling(Request $request) {
        $request->validate([
            'use_lodestone'     => 'nullable|boolean',
            'character_id'      => 'nullable|numeric',
            'character_job'     => ['nullable', Rule::in(array_keys(config('ffxiv.classjob')))],
            'character_level'   => 'nullable|numeric|max:'.config('ffxiv.leveling_data.level_data.level_cap'),
            'character_exp'     => 'nullable|numeric',
            'character_highest' => 'nullable|numeric|max:'.config('ffxiv.leveling_data.level_data.level_cap'),
            'character_road'    => 'nullable|boolean',
            'gear_brand_new'    => 'nullable|boolean',
            'gear_earring'      => 'nullable|boolean',
            'temp_fc'           => 'nullable|in:1,2,3',
            'temp_food'         => 'nullable|boolean',
            'temp_rested'       => 'nullable|boolean',
            'override'          => 'nullable|numeric',
        ]);

        if ($request->get('use_lodestone')) {
            if ($request->get('character_id') && ($request->get('character_job') && in_array($request->get('character_job'), array_keys(config('ffxiv.classjob'))))) {
                // Request and parse data from lodestone
                $response = Http::retry(3, 100, throw: false)->get('https://na.finalfantasyxiv.com/lodestone/character/'.$request->get('character_id').'/class_job/');

                if ($response->successful()) {
                    // Parse data
                    $response = collect((new ParseCharacterClassJobs)->handle($response->getBody())['classjobs']);

                    // Record the highest level combat class/job
                    $request->merge(['character_highest' => $response->whereIn('ClassID', array_keys(config('ffxiv.classjob')))->sortByDesc('Level')->first()->Level]);

                    // Pick out the specified class/job's data
                    $classData = $response->where('ClassID', $request->get('character_job'))->first();

                    if ($classData) {
                        // If class info was retrieved successfully, record data
                        $request->merge([
                            'character_level' => $classData->Level && $classData->Level > 0 ? $classData->Level : 1,
                            'character_exp'   => $classData->ExpLevel ?? 0,
                        ]);
                    }
                } else {
                    flash('Request to The Lodestone failed; please try again later.')->error();
                }
            } else {
                flash('Please enter both a character ID and valid combat class/job to retrieve information from The Lodestone.')->error();
            }
        } elseif ($request->all()) {
            // Ensure that the bool is set even if disabled
            // so that the manual entry options display persistently
            $request->merge(['use_lodestone' => 0]);
        }

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
                ($request->get('character_highest') > $request->get('character_level') ? 50 : 0) +
                (((config('ffxiv.leveling_data.level_data.level_cap') - 9) <= config('ffxiv.leveling_data.gear.earring.max')) && $request->get('gear_earring') ? 30 : 0),
        ];

        // Set up the rested EXP pool if relevant
        if ($request->get('temp_rested')) {
            // Rested EXP is a pool of 1.5 levels worth, conveyed via a 50% boost to EXP gain until spent
            $restedPool = 1.5;
        }

        for ($level = ($request->get('character_level') && $request->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? $request->get('character_level') : 1); $level < config('ffxiv.leveling_data.level_data.level_cap'); $level++) {
            // Calculate EXP remaining to level
            $remainingExp = config('ffxiv.leveling_data.level_data.level_exp.'.$level);
            if ($request->get('character_exp') && $request->get('character_level') && $request->get('character_level') == $level) {
                // If the level in question is the current character level,
                // deduct the current EXP value
                $remainingExp -= $request->get('character_exp');
            }

            // Calculate dungeon values
            if ($level >= 15) {
                // Determine the relevant EXP bonus value and convert it from percentage
                $dungeonBonus = ((int) collect($bonus)->filter(function ($value, $key) use ($level) {
                    return $key <= $level;
                })->last());
                $dungeonBonus = $dungeonBonus ? (1 + ($dungeonBonus / 100)) : 1;

                // Determine the highest level "leveling" dungeon available
                $dungeonSearch = collect(config('ffxiv.leveling_data.dungeon.dungeon_data'))->filter(function ($value, $key) use ($level) {
                    return $key <= $level;
                })->take(-1);
                $dungeon[$level]['level'] = $dungeonSearch->keys()->last();

                // If there was excess EXP from the last calculated level, remove it from the remaining EXP
                $dungeon[$level]['remaining_exp'] = $remainingExp - ($dungeon[$level - 1]['overage'] ?? 0);

                if ($dungeon[$level]['level']) {
                    // If a dungeon was successfully located, calculate estimated EXP
                    // and from there, estimated runs to next level and excess EXP
                    $dungeon[$level]['exp'] = round((int) $dungeonSearch->last() * $dungeonBonus);
                    $dungeon[$level]['runs'] = max(1, ceil($dungeon[$level]['remaining_exp'] / $dungeon[$level]['exp']));

                    // Calculate rested EXP use
                    if ($request->get('temp_rested') && $restedPool > 0) {
                        // Rested EXP gained from all intended runs of the dungeon, limited by how much remains in the pool (approximately)
                        $dungeon[$level]['rested'] = round(min(min(1, $restedPool) * config('ffxiv.leveling_data.level_data.level_exp.'.$level), ((int) $dungeonSearch->last() * .5) * $dungeon[$level]['runs']));

                        if ($dungeon[$level]['rested']) {
                            // Recalculate EXP and runs required so the following values are calculated appropriately
                            $dungeon[$level]['exp'] = round(((int) $dungeonSearch->last() * $dungeonBonus) + ($dungeon[$level]['rested'] / $dungeon[$level]['runs']));
                            $dungeon[$level]['runs'] = ceil($dungeon[$level]['remaining_exp'] / $dungeon[$level]['exp']);

                            // Adjust the rested pool down accordingly
                            $dungeon[$level]['rested_boost'] = $dungeon[$level]['rested'] / config('ffxiv.leveling_data.level_data.level_exp.'.$level);
                            $restedPool -= $dungeon[$level]['rested_boost'];
                        }
                    }

                    $dungeon[$level]['overage'] = (($dungeon[$level]['exp'] * $dungeon[$level]['runs']) - $dungeon[$level]['remaining_exp']);
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
                    if ($level < (config('ffxiv.leveling_data.level_data.level_cap') - 10)) {
                        $deepDungeonBonus += 1;
                    } else {
                        $deepDungeonBonus += 0.5;
                    }
                }
                if ($request->get('character_road') && $level < (config('ffxiv.leveling_data.level_data.level_cap') - 10)) {
                    $deepDungeonBonus += 1;
                }

                // If there was excess EXP from the last calculated level, remove it from the remaining EXP
                $deepDungeon[$level]['remaining_exp'] = $remainingExp - ($deepDungeon[$level - 1]['overage'] ?? 0);

                // with a general formula of (level + modifier) * base EXP value
                // and then * floor multipler and any applicable EXP bonuses
                $deepDungeon[$level]['exp'] = round(($level + config('ffxiv.leveling_data.'.strtolower($deepDungeon[$level]['dungeon']).'.level_data.'.$level)[0]) * config('ffxiv.leveling_data.'.strtolower($deepDungeon[$level]['dungeon']).'.level_data.'.$level)[1] * $floorMult * $deepDungeonBonus);

                $deepDungeon[$level]['runs'] = ceil($deepDungeon[$level]['remaining_exp'] / $deepDungeon[$level]['exp']);
                $deepDungeon[$level]['overage'] = (($deepDungeon[$level]['exp'] * $deepDungeon[$level]['runs']) - $deepDungeon[$level]['remaining_exp']);
                $deepDungeon[$level]['total_runs'] = (isset($deepDungeon[$level - 1]['total_runs']) && !in_array($level, array_keys(config('ffxiv.leveling_data.level_data.level_ranges'))) ? $deepDungeon[$level - 1]['total_runs'] : 0) + $deepDungeon[$level]['runs'];
            }

            // Calculate Frontline values
            if ($level >= 30 && config('ffxiv.leveling_data.frontline.level_data.'.$level)) {
                // If there was excess EXP from the last calculated level, remove it from the remaining EXP
                $frontline[$level]['remaining_exp'] = $remainingExp - ($frontline[$level - 1]['overage'] ?? 0);

                // Frontline awards flat EXP unimpacted by any bonuses,
                // but varies depending on a win or loss, so average those values
                // Otherwise the EXP formula is similar to deep dungeons'
                $frontline[$level]['loss'] = ($level + config('ffxiv.leveling_data.frontline.level_data.'.$level)[0]) * config('ffxiv.leveling_data.frontline.level_data.'.$level)[1];
                $frontline[$level]['win'] = $frontline[$level]['loss'] * config('ffxiv.leveling_data.frontline.win_mult');
                $frontline[$level]['avg_exp'] = ($frontline[$level]['loss'] + $frontline[$level]['win']) / 2;

                $frontline[$level]['runs'] = ceil($frontline[$level]['remaining_exp'] / $frontline[$level]['avg_exp']);
                $frontline[$level]['overage'] = (($frontline[$level]['avg_exp'] * $frontline[$level]['runs']) - $frontline[$level]['remaining_exp']);
                $frontline[$level]['total_runs'] = (isset($frontline[$level - 1]['total_runs']) && !in_array($level, array_keys(config('ffxiv.leveling_data.level_data.level_ranges'))) ? $frontline[$level - 1]['total_runs'] : 0) + $frontline[$level]['runs'];
            }
        }

        return view('leveling.index', [
            'bonus'       => $bonus,
            'dungeon'     => $dungeon ?? null,
            'deepDungeon' => $deepDungeon ?? null,
            'frontline'   => $frontline ?? null,
        ]);
    }
}
