<ul class="nav nav-pills justify-content-end" role="tablist">
    @foreach (['dungeon' => $dungeon, 'deep-dungeon' => $deepDungeon, 'society-quest' => $societyQuest, 'frontline' => $frontline] as $label => $source)
        @if (isset($source[$floor]) || isset($source[$range['ceiling']]))
            <li class="nav-item">
                <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $label }}-tab-{{ $floor }}" data-bs-toggle="tab" data-bs-target="#exp-{{ $label }}-{{ $floor }}" type="button" role="tab"
                    aria-controls="exp-{{ $label }}-{{ $floor }}" aria-selected="true">
                    {{ ucwords(str_replace('-', ' ', $label)) }}
                    @if (isset($source[$range['ceiling']]['total_runs']))
                        ({{ $source[$range['ceiling']]['total_runs'] ?? '-' }}
                        @switch($label)
                            @case('frontline')
                                match{{ $source[$range['ceiling']]['total_runs'] > 1 ? 'es' : '' }})
                            @break

                            @case('society-quest')
                                day{{ $source[$range['ceiling']]['total_runs'] > 1 ? 's' : '' }})
                            @break

                            @default
                                run{{ $source[$range['ceiling']]['total_runs'] > 1 ? 's' : '' }})
                        @endswitch
                    @else
                        (Partial)
                    @endif
                </a>
            </li>
        @endif
    @endforeach
</ul>
<div class="row g-md-0 ps-2 ps-md-0">
    <div class="col-4 col-md-4">
        <div class="row ms-md-2 text-center">
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                <div class="col-12 col-md-4 font-weight-bold">Level</div>
                <div class="col-12 col-md-8 font-weight-bold">EXP to Next</div>
            </div>

            @for ($level = max(request()->get('character_level') && request()->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? request()->get('character_level') : 1, $floor); $level <= ($range['ceiling'] == config('ffxiv.leveling_data.level_data.level_cap') ? config('ffxiv.leveling_data.level_data.level_cap') - 1 : $range['ceiling']); $level++)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-light-subtle border-top">
                    <div class="col-12 col-md-4">{{ $level }}</div>
                    <div class="col-12 col-md-8">
                        {{ number_format(config('ffxiv.leveling_data.level_data.level_exp.' . $level)) }}</div>
                </div>
            @endfor
        </div>
    </div>

    <div class="col col-md-8">
        <div class="tab-content" id="nav-tabContent">
            @if (isset($dungeon[$floor]) || ($range['ceiling'] != config('ffxiv.leveling_data.level_data.level_cap') ? isset($dungeon[$range['ceiling']]) : isset($dungeon[$range['ceiling'] - 1])))
                <div class="tab-pane show active" id="exp-dungeon-{{ $floor }}" role="tabpanel" aria-labelledby="dungeon-tab-{{ $floor }}" tabindex="0">
                    <div class="row ms-md-2 text-center">
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                            <div class="col-6 col-md-2 font-weight-bold">Dungeon</div>
                            <div class="col-6 col-md-3 font-weight-bold">EXP (Est.)</div>
                            <div class="col-3 col-md-2 font-weight-bold">Runs</div>
                            <div class="col-3 col-md-2 font-weight-bold">Overage</div>
                            <div class="col-6 col-md-3 font-weight-bold">Total Runs</div>
                        </div>

                        @for ($level = max(request()->get('character_level') && request()->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? request()->get('character_level') : 1, $floor); $level <= ($range['ceiling'] == config('ffxiv.leveling_data.level_data.level_cap') ? config('ffxiv.leveling_data.level_data.level_cap') - 1 : $range['ceiling']); $level++)
                            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-light-subtle border-top">
                                <div class="col-6 col-md-2">{{ $dungeon[$level]['level'] ?? '-' }}</div>
                                <div class="col-6 col-md-3">
                                    {{ isset($dungeon[$level]['exp']) ? number_format($dungeon[$level]['exp']) : '-' }}
                                    @if (isset($dungeon[$level]['rested_used']) && $dungeon[$level]['rested_used'])
                                        <span class="text-primary" data-bs-toggle="tooltip" data-toggle="tooltip"
                                            title="Boosted by rested EXP (+{{ number_format(round($dungeon[$level]['rested'] / $dungeon[$level]['runs'])) }} EXP (Est.) & {{ $dungeon[$level]['rested_used'] }}% used per run)"><strong>*</strong></span>
                                    @endif
                                </div>
                                <div class="col-3 col-md-2">{{ $dungeon[$level]['runs'] ?? '' }}</div>
                                <div class="col-3 col-md-2">
                                    {{ isset($dungeon[$level]['overage']) ? number_format($dungeon[$level]['overage']) : '' }}
                                </div>
                                <div class="col-6 col-md-3">{{ $dungeon[$level]['total_runs'] ?? '-' }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif
            @if (isset($deepDungeon[$floor]) || isset($deepDungeon[$range['ceiling']]))
                <div class="tab-pane" id="exp-deep-dungeon-{{ $floor }}" role="tabpanel" aria-labelledby="deep-dungeon-tab-{{ $floor }}" tabindex="0">
                    <div class="row ms-md-2 text-center">
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                            <div class="col-6 col-md-2 font-weight-bold">{{ $deepDungeon[$range['ceiling']]['dungeon'] ?? 'Dungeon' }}</div>
                            <div class="col-6 col-md-3 font-weight-bold">EXP</div>
                            <div class="col-3 col-md-2 font-weight-bold">Runs</div>
                            <div class="col-3 col-md-2 font-weight-bold">Overage</div>
                            <div class="col-6 col-md-3 font-weight-bold">Total Runs</div>
                        </div>

                        @for ($level = max(request()->get('character_level') && request()->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? request()->get('character_level') : 1, $floor); $level <= ($range['ceiling'] == config('ffxiv.leveling_data.level_data.level_cap') ? config('ffxiv.leveling_data.level_data.level_cap') - 1 : $range['ceiling']); $level++)
                            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-light-subtle border-top">
                                <div class="col-6 col-md-2">{{ $deepDungeon[$level]['level'] ?? '-' }}</div>
                                <div class="col-6 col-md-3">
                                    {{ isset($deepDungeon[$level]['exp']) ? number_format($deepDungeon[$level]['exp']) : '-' }}
                                </div>
                                <div class="col-3 col-md-2">{{ $deepDungeon[$level]['runs'] ?? '' }}</div>
                                <div class="col-3 col-md-2">
                                    {{ isset($deepDungeon[$level]['overage']) ? number_format($deepDungeon[$level]['overage']) : '' }}
                                </div>
                                <div class="col-6 col-md-3">{{ $deepDungeon[$level]['total_runs'] ?? '' }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif
            @if (isset($societyQuest[$floor]) || ($range['ceiling'] != config('ffxiv.leveling_data.level_data.level_cap') ? isset($societyQuest[$range['ceiling']]) : isset($societyQuest[$range['ceiling'] - 1])))
                <div class="tab-pane" id="exp-society-quest-{{ $floor }}" role="tabpanel" aria-labelledby="society-quest-tab-{{ $floor }}" tabindex="0">
                    <div class="row ms-md-2 text-center">
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                            <div class="col-6 col-md-2 font-weight-bold">Society</div>
                            <div class="col-6 col-md-3 font-weight-bold">
                                EXP
                                <span class="text-primary" data-bs-toggle="tooltip" data-toggle="tooltip" title="At Bloodsworn rank"><strong>*</strong></span>
                            </div>
                            <div class="col-3 col-md-2 font-weight-bold">Days</div>
                            <div class="col-3 col-md-2 font-weight-bold">Overage</div>
                            <div class="col-6 col-md-3 font-weight-bold">Total Days</div>
                        </div>

                        @for ($level = max(request()->get('character_level') && request()->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? request()->get('character_level') : 1, $floor); $level <= ($range['ceiling'] == config('ffxiv.leveling_data.level_data.level_cap') ? config('ffxiv.leveling_data.level_data.level_cap') - 1 : $range['ceiling']); $level++)
                            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-light-subtle border-top">
                                <div class="col-6 col-md-2">{{ $societyQuest[$level]['dungeon'] ?? '-' }}</div>
                                <div class="col-6 col-md-3">
                                    {{ isset($societyQuest[$level]['day_exp']) ? number_format($societyQuest[$level]['day_exp']) : '-' }}
                                    @if (isset($societyQuest[$level]['exp']))
                                        <span class="text-primary" data-bs-toggle="tooltip" data-toggle="tooltip" title="3 quests per day, at {{ number_format($societyQuest[$level]['exp']) }} per quest"><strong>*</strong></span>
                                    @endif
                                </div>
                                <div class="col-3 col-md-2">{{ $societyQuest[$level]['runs'] ?? '' }}</div>
                                <div class="col-3 col-md-2">
                                    {{ isset($societyQuest[$level]['overage']) ? number_format($societyQuest[$level]['overage']) : '' }}
                                </div>
                                <div class="col-6 col-md-3">{{ $societyQuest[$level]['total_runs'] ?? '-' }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif
            @if (isset($frontline[$floor]) || ($range['ceiling'] != config('ffxiv.leveling_data.level_data.level_cap') ? isset($frontline[$range['ceiling']]) : isset($frontline[$range['ceiling'] - 1])))
                <div class="tab-pane" id="exp-frontline-{{ $floor }}" role="tabpanel" aria-labelledby="frontline-tab-{{ $floor }}" tabindex="0">
                    <div class="row ms-md-2 text-center">
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                            <div class="col-6 col-md-3 font-weight-bold">Avg. EXP</div>
                            <div class="col-6 col-md-3 font-weight-bold">Matches</div>
                            <div class="col-5 col-md-3 font-weight-bold">Overage</div>
                            <div class="col-7 col-md-3 font-weight-bold">Total Matches</div>
                        </div>

                        @for ($level = max(request()->get('character_level') && request()->get('character_level') < config('ffxiv.leveling_data.level_data.level_cap') ? request()->get('character_level') : 1, $floor); $level <= ($range['ceiling'] == config('ffxiv.leveling_data.level_data.level_cap') ? config('ffxiv.leveling_data.level_data.level_cap') - 1 : $range['ceiling']); $level++)
                            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-light-subtle border-top">
                                <div class="col-6 col-md-3">
                                    {{ isset($frontline[$level]['avg_exp']) ? number_format($frontline[$level]['avg_exp']) : '-' }}
                                </div>
                                <div class="col-6 col-md-3">{{ $frontline[$level]['runs'] ?? '' }}</div>
                                <div class="col-6 col-md-3">
                                    {{ isset($frontline[$level]['overage']) ? number_format($frontline[$level]['overage']) : '' }}
                                </div>
                                <div class="col-6 col-md-3">{{ $frontline[$level]['total_runs'] ?? '-' }}</div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
