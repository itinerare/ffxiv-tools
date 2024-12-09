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
        $inputs = [
            'use_lodestone'     => 'nullable|boolean',
            'character_id'      => 'nullable|numeric',
            'character_job'     => ['nullable', Rule::in(array_keys((array) config('ffxiv.classjob')))],
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
        ];
        $request->validate($inputs);

        $request = $this->handleSettingsCookie($request, 'levelingSettings', $inputs);

        if ($request->get('use_lodestone')) {
            if ($request->get('character_id') && ($request->get('character_job') && in_array($request->get('character_job'), array_keys((array) config('ffxiv.classjob'))))) {
                // Request and parse data from lodestone
                $response = Http::retry(3, 100, throw: false)->get('https://na.finalfantasyxiv.com/lodestone/character/'.$request->get('character_id').'/class_job/');

                if ($response->successful()) {
                    // Parse data
                    $response = collect((new ParseCharacterClassJobs)->handle($response->getBody())['classjobs']);

                    // Record the highest level combat class/job
                    $request->merge(['character_highest' => $response->whereIn('ClassID', array_keys((array) config('ffxiv.classjob')))->sortByDesc('Level')->first()->Level]);

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
            ($request->get('override') ?? 0);

        // Calculate FC bonus separately, since it can also impact armoury bonus
        $fcBonus = 1 + ($request->get('temp_fc') ? ($request->get('temp_fc') * 5) / 100 : 0);

        // Add level range specific bonuses
        $bonus = [
            1  => $bonusBase +
                ($request->get('character_highest') > $request->get('character_level') ? floor(100 * $fcBonus) : 0) +
                ($request->get('character_road') ? 100 : 0) +
                ($request->get('gear_brand_new') ? 30 : 0) +
                ($request->get('gear_earring') ? 30 : 0),
            61 => $bonusBase +
                ($request->get('character_highest') > $request->get('character_level') ? floor(100 * $fcBonus) : 0) +
                ($request->get('character_road') ? 100 : 0) +
                ($request->get('gear_earring') ? 30 : 0),
            ((int) config('ffxiv.leveling_data.level_data.level_cap') - 9) => $bonusBase +
                ($request->get('character_highest') > $request->get('character_level') ? floor(50 * $fcBonus) : 0) +
                ((((int) config('ffxiv.leveling_data.level_data.level_cap') - 9) <= config('ffxiv.leveling_data.gear.earring.max')) && $request->get('gear_earring') ? 30 : 0),
        ];

        // Set up the rested EXP pool if relevant
        if ($request->get('temp_rested')) {
            // Rested EXP is a pool of 1.5 levels worth, conveyed via a 50% boost to EXP gain until spent
            $restedPool = $restedRemaining = 1.5;
        }

        for ($level = ($request->get('character_level') && $request->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? $request->get('character_level') : 1); $level < config('ffxiv.leveling_data.level_data.level_cap'); $level++) {
            // Calculate EXP remaining to level
            $remainingExp = (int) config('ffxiv.leveling_data.level_data.level_exp.'.$level);
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
                $dungeon[$level]['remaining_exp'] = max(0, $remainingExp - ($dungeon[$level - 1]['overage'] ?? 0));
                if ($dungeon[$level]['remaining_exp'] == 0) {
                    // If there is excess overage, carry it forward
                    $dungeon[$level]['overage'] = ($dungeon[$level - 1]['overage'] ?? 0) - $remainingExp;
                }

                if ($dungeon[$level]['level']) {
                    if ($dungeon[$level]['remaining_exp'] > 0) {
                        // If a dungeon was successfully located, calculate estimated EXP
                        // and from there, estimated runs to next level and excess EXP
                        $dungeon[$level]['exp'] = round((int) $dungeonSearch->last() * $dungeonBonus);
                        $dungeon[$level]['runs'] = max(0, ceil($dungeon[$level]['remaining_exp'] / $dungeon[$level]['exp']));

                        // Calculate rested EXP use
                        if ($request->get('temp_rested') && ($restedRemaining ?? 0) > 0 && $dungeon[$level]['remaining_exp'] > 0) {
                            // Rested EXP gained from all intended runs of the dungeon, limited by how much remains in the pool (approximately)
                            $dungeon[$level]['rested'] = round(min(min(1, $restedRemaining) * (int) config('ffxiv.leveling_data.level_data.level_exp.'.$level), ((int) $dungeonSearch->last() * .5) * $dungeon[$level]['runs']));

                            if ($dungeon[$level]['rested']) {
                                // Recalculate EXP and runs required so the following values are calculated appropriately
                                $dungeon[$level]['exp'] = round(((int) $dungeonSearch->last() * $dungeonBonus) + ($dungeon[$level]['rested'] / $dungeon[$level]['runs']));
                                $dungeon[$level]['runs'] = max(0, ceil($dungeon[$level]['remaining_exp'] / $dungeon[$level]['exp']));

                                // Adjust the rested pool down accordingly
                                $restedRemaining -= max(0, $dungeon[$level]['rested_used'] = $dungeon[$level]['rested'] / (int) config('ffxiv.leveling_data.level_data.level_exp.'.$level));

                                // Calculate % of rested EXP used
                                $dungeon[$level]['rested_used'] = round((($dungeon[$level]['rested_used'] / $restedPool) / $dungeon[$level]['runs']) * 100);
                            }
                        }
                    } else {
                        // If overage accounts for the entirety of a level, set some empty values
                        // This helps keep the frontend display clear as to what's happening
                        $dungeon[$level]['exp'] = null;
                        $dungeon[$level]['runs'] = 0;
                    }

                    $dungeon[$level]['overage'] = (($dungeon[$level]['exp'] * $dungeon[$level]['runs']) - $dungeon[$level]['remaining_exp']) + ($dungeon[$level]['overage'] ?? 0);
                    // The total runs counter resets at the start of a level range
                    // since the frontend displays ranges separately
                    $dungeon[$level]['total_runs'] = (isset($dungeon[$level - 1]['total_runs']) && !in_array($level, array_keys((array) config('ffxiv.leveling_data.level_data.level_ranges'))) ? $dungeon[$level - 1]['total_runs'] : 0) + $dungeon[$level]['runs'];
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
                    if ($level < ((int) config('ffxiv.leveling_data.level_data.level_cap') - 10)) {
                        $deepDungeonBonus += 1;
                    } else {
                        $deepDungeonBonus += 0.5;
                    }
                }
                if ($request->get('character_road') && $level < ((int) config('ffxiv.leveling_data.level_data.level_cap') - 10)) {
                    $deepDungeonBonus += 1;
                }

                // If there was excess EXP from the last calculated level, remove it from the remaining EXP
                $deepDungeon[$level]['remaining_exp'] = max(0, $remainingExp - ($deepDungeon[$level - 1]['overage'] ?? 0));
                if ($deepDungeon[$level]['remaining_exp'] == 0) {
                    // If there is excess overage, carry it forward
                    $deepDungeon[$level]['overage'] = ($deepDungeon[$level - 1]['overage'] ?? 0) - $remainingExp;
                }

                if ($deepDungeon[$level]['remaining_exp'] > 0) {
                    // with a general formula of (level + modifier) * base EXP value
                    // and then * floor multipler and any applicable EXP bonuses
                    $deepDungeon[$level]['exp'] = round(($level + config('ffxiv.leveling_data.'.strtolower($deepDungeon[$level]['dungeon']).'.level_data.'.$level)[0]) * config('ffxiv.leveling_data.'.strtolower($deepDungeon[$level]['dungeon']).'.level_data.'.$level)[1] * $floorMult * $deepDungeonBonus);

                    $deepDungeon[$level]['runs'] = max(0, ceil($deepDungeon[$level]['remaining_exp'] / $deepDungeon[$level]['exp']));
                } else {
                    // If overage accounts for the entirety of a level, set some empty values
                    // This helps keep the frontend display clear as to what's happening
                    $deepDungeon[$level]['exp'] = null;
                    $deepDungeon[$level]['runs'] = 0;
                }

                $deepDungeon[$level]['overage'] = (($deepDungeon[$level]['exp'] * $deepDungeon[$level]['runs']) - $deepDungeon[$level]['remaining_exp']) + ($deepDungeon[$level]['overage'] ?? 0);
                $deepDungeon[$level]['total_runs'] = (isset($deepDungeon[$level - 1]['total_runs']) && !in_array($level, array_keys((array) config('ffxiv.leveling_data.level_data.level_ranges'))) ? $deepDungeon[$level - 1]['total_runs'] : 0) + $deepDungeon[$level]['runs'];
            }

            // Calculate Frontline values
            if ($level >= 30 && config('ffxiv.leveling_data.frontline.level_data.'.$level)) {
                // If there was excess EXP from the last calculated level, remove it from the remaining EXP
                $frontline[$level]['remaining_exp'] = max(0, $remainingExp - ($frontline[$level - 1]['overage'] ?? 0));
                if ($frontline[$level]['remaining_exp'] == 0) {
                    // If there is excess overage, carry it forward
                    $frontline[$level]['overage'] = ($frontline[$level - 1]['overage'] ?? 0) - $remainingExp;
                }

                if ($frontline[$level]['remaining_exp'] > 0) {
                    // Frontline awards flat EXP unimpacted by any bonuses,
                    // but varies depending on a win or loss, so average those values
                    // Otherwise the EXP formula is similar to deep dungeons'
                    $frontline[$level]['loss'] = ($level + config('ffxiv.leveling_data.frontline.level_data.'.$level)[0]) * config('ffxiv.leveling_data.frontline.level_data.'.$level)[1];
                    $frontline[$level]['win'] = $frontline[$level]['loss'] * (int) config('ffxiv.leveling_data.frontline.win_mult');
                    $frontline[$level]['avg_exp'] = ($frontline[$level]['loss'] + $frontline[$level]['win']) / 2;

                    $frontline[$level]['runs'] = max(0, ceil($frontline[$level]['remaining_exp'] / $frontline[$level]['avg_exp']));
                } else {
                    // If overage accounts for the entirety of a level, set some empty values
                    // This helps keep the frontend display clear as to what's happening
                    $frontline[$level]['avg_exp'] = null;
                    $frontline[$level]['runs'] = 0;
                }

                $frontline[$level]['overage'] = (($frontline[$level]['avg_exp'] * $frontline[$level]['runs']) - $frontline[$level]['remaining_exp']) + ($frontline[$level]['overage'] ?? 0);
                $frontline[$level]['total_runs'] = (isset($frontline[$level - 1]['total_runs']) && !in_array($level, array_keys((array) config('ffxiv.leveling_data.level_data.level_ranges'))) ? $frontline[$level - 1]['total_runs'] : 0) + $frontline[$level]['runs'];
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
