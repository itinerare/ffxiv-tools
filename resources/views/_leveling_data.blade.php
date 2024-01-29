<ul class="nav nav-pills justify-content-end" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="dungeon-tab-{{ $floor }}" data-bs-toggle="tab" data-bs-target="#exp-dungeon-{{ $floor }}" type="button" role="tab" aria-controls="exp-dungeon-{{ $floor }}" aria-selected="true">
            Dungeon
            ({{ $dungeon[$range['ceiling']]['total_runs'] ?? $dungeon[$range['ceiling']-1]['total_runs'] }} runs)
        </a>
    </li>
    @if($floor != 71)
        <li class="nav-item">
            <a class="nav-link" id="deep-dungeon-tab-{{ $floor }}" data-bs-toggle="tab" data-bs-target="#exp-deep-dungeon-{{ $floor }}" type="button" role="tab" aria-controls="exp-deep-dungeon-{{ $floor }}" aria-selected="false">
                Deep Dungeon
                ({{ $deepDungeon[$range['ceiling']]['total_runs'] ?? $deepDungeon[$range['ceiling']-1]['total_runs'] }} runs)
            </a>
        </li>
    @endif
    <li class="nav-item">
        <a class="nav-link" id="frontline-tab-{{ $floor }}" data-bs-toggle="tab" data-bs-target="#exp-frontline-{{ $floor }}" type="button" role="tab" aria-controls="exp-frontline-{{ $floor }}" aria-selected="false">
            PvP (Frontline)
            ({{ $frontline[$range['ceiling']]['total_runs'] ?? $frontline[$range['ceiling']-1]['total_runs'] }} runs)
        </a>
    </li>
</ul>
<div class="row g-md-0 ps-2 ps-md-0">
    <div class="col-4 col-md-4">
        <div class="row ms-md-2 text-center">
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                <div class="col-12 col-md-4 font-weight-bold">Level</div>
                <div class="col-12 col-md-8 font-weight-bold">EXP to Next</div>
            </div>

            @for ($level = max(request()->get('character_level') ?? 0, $floor); $level <= ($range['ceiling'] == 90 ? 89 : $range['ceiling']); $level++)
                <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-primary border-top">
                    <div class="col-12 col-md-4">{{ $level }}</div>
                    <div class="col-12 col-md-8">{{ config('ffxiv.leveling_data.level_data.level_exp.'.$level) ?? '-' }}</div>
                </div>
            @endfor
        </div>
    </div>

    <div class="col col-md-8">
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane show active" id="exp-dungeon-{{ $floor }}" role="tabpanel" aria-labelledby="dungeon-tab-{{ $floor }}" tabindex="0">
                <div class="row ms-md-2 text-center">
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                        <div class="col-6 col-md-2 font-weight-bold">Dungeon</div>
                        <div class="col-6 col-md-3 font-weight-bold">EXP (Est.)</div>
                        <div class="col-3 col-md-2 font-weight-bold">Runs</div>
                        <div class="col-3 col-md-2 font-weight-bold">Overage</div>
                        <div class="col-6 col-md-3 font-weight-bold">Total Runs</div>
                    </div>

                    @for ($level = max(request()->get('character_level') ?? 0, $floor); $level <= ($range['ceiling'] == 90 ? 89 : $range['ceiling']); $level++)
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-primary border-top">
                            <div class="col-6 col-md-2">{{ $dungeon[$level]['level'] ?? '-' }}</div>
                            <div class="col-6 col-md-3">{{ $dungeon[$level]['exp'] ?? '' }}</div>
                            <div class="col-3 col-md-2">{{ $dungeon[$level]['runs'] ?? '' }}</div>
                            <div class="col-3 col-md-2">{{ $dungeon[$level]['overage'] ?? '' }}</div>
                            <div class="col-6 col-md-3">{{ $dungeon[$level]['total_runs'] ?? '-' }}</div>
                        </div>
                    @endfor
                </div>
            </div>
            @if($floor != 71)
                <div class="tab-pane" id="exp-deep-dungeon-{{ $floor }}" role="tabpanel" aria-labelledby="deep-dungeon-tab-{{ $floor }}" tabindex="0">
                    <div class="row ms-md-2 text-center">
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                            <div class="col-6 col-md-2 font-weight-bold">{{ $deepDungeon[$range['ceiling']-1]['dungeon'] ?? 'Dungeon' }}</div>
                            <div class="col-6 col-md-3 font-weight-bold">EXP</div>
                            <div class="col-3 col-md-2 font-weight-bold">Runs</div>
                            <div class="col-3 col-md-2 font-weight-bold">Overage</div>
                            <div class="col-6 col-md-3 font-weight-bold">Total Runs</div>
                        </div>

                        @for ($level = max(request()->get('character_level') ?? 0, $floor); $level <= ($range['ceiling'] == 90 ? 89 : $range['ceiling']); $level++)
                            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-primary border-top">
                                <div class="col-6 col-md-2">{{ $deepDungeon[$level]['level'] ?? '-' }}</div>
                                <div class="col-6 col-md-3">{{ $deepDungeon[$level]['exp'] ?? '' }}</div>
                                <div class="col-3 col-md-2">{{ $deepDungeon[$level]['runs'] ?? '' }}</div>
                                <div class="col-3 col-md-2">{{ $deepDungeon[$level]['overage'] ?? '' }}</div>
                                <div class="col-6 col-md-3">{{ $deepDungeon[$level]['total_runs'] ?? '' }}</div>
                            </div>
                        @endfor
                    </div>
            </div>
            @endif
            <div class="tab-pane" id="exp-frontline-{{ $floor }}" role="tabpanel" aria-labelledby="frontline-tab-{{ $floor }}" tabindex="0">
                <div class="row ms-md-2 text-center">
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-2 border-info border-bottom">
                        <div class="col-6 col-md-3 font-weight-bold">Avg. EXP</div>
                        <div class="col-6 col-md-3 font-weight-bold">Matches</div>
                        <div class="col-5 col-md-3 font-weight-bold">Overage</div>
                        <div class="col-7 col-md-3 font-weight-bold">Total Matches</div>
                    </div>

                    @for ($level = max(request()->get('character_level') ?? 0, $floor); $level <= ($range['ceiling'] == 90 ? 89 : $range['ceiling']); $level++)
                        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 border-primary border-top">
                            <div class="col-6 col-md-3">{{ $frontline[$level]['avg_exp'] ?? '-' }}</div>
                            <div class="col-6 col-md-3">{{ $frontline[$level]['runs'] ?? '' }}</div>
                            <div class="col-6 col-md-3">{{ $frontline[$level]['overage'] ?? '' }}</div>
                            <div class="col-6 col-md-3">{{ $frontline[$level]['total_runs'] ?? '-' }}</div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>
