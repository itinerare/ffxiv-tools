@extends('layouts.app')

@section('title')
    ・ Leveling Calculator
@endsection

@section('content')
    <div class="accordion mb-4" id="SettingsSelect">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ request()->all() ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#Settings" aria-expanded="true" aria-controls="Settings">
                    Settings
                    @if (request()->get('character_level'))
                        - Level {{ request()->get('character_level') }}
                        @if (request()->get('use_lodestone') && request()->get('character_job'))
                            {{ config('ffxiv.classjob')[request()->get('character_job')] }}
                        @endif
                        @if (request()->get('character_exp'))
                            at
                            {{ number_format(request()->get('character_exp')) }}/{{ number_format(config('ffxiv.leveling_data.level_data.level_exp.' . request()->get('character_level'))) }}
                            EXP
                            ({{ round((request()->get('character_exp') / config('ffxiv.leveling_data.level_data.level_exp.' . request()->get('character_level'))) * 100, 2) }}%)
                        @endif
                    @endif
                </button>
            </h2>
            <div id="Settings" class="accordion-collapse collapse {{ request()->all() ? '' : 'show' }}" data-bs-parent="#SettingsSelect">
                <div class="accordion-body">
                    <p>Please note that no data is saved to the server or your computer; if you wish to save your settings
                        for later, instead save the URL after submitting them!</p>
                    {{ html()->form('GET')->open() }}
                    <h5>Character</h5>
                    <div class="mb-3">
                        {{ html()->checkbox('use_lodestone', request()->get('use_lodestone') ?? 1)->class('form-check-input')->id('useLodestone') }}
                        {{ html()->label('Retrieve info from The Lodestone', 'use_lodestone')->class('form-check-label') }}<br />
                        Please note that Lodestone data is only updated on logout! Please update manually if wanting to use
                        the most up-to-date values while playing.
                    </div>
                    <div id="lodestoneContainer" class="row d-none">
                        <div class="col-md mb-3">
                            {{ html()->label('Character Lodestone ID', 'character_id')->class('form-label') }}
                            {{ html()->input('number', 'character_id', request()->get('character_id') ?? null)->class('form-control') }}
                        </div>

                        <div class="col-md mb-3">
                            {{ html()->label('Class/Job', 'character_job')->class('form-label') }}
                            {{ html()->select('character_job', config('ffxiv.classjob'), request()->get('character_job') ?? null)->class('form-select')->placeholder('Select Class/Job') }}
                        </div>
                    </div>
                    <div id="manualContainer" class="row d-none">
                        <div class="col-md mb-3">
                            {{ html()->label('Current Level', 'character_level')->class('form-label') }}
                            {{ html()->input('number', 'character_level', request()->get('character_level') ?? null)->class('form-control')->attribute('max', config('ffxiv.leveling_data.level_data.level_cap')) }}
                        </div>

                        <div class="col-md mb-3">
                            {{ html()->label('Current EXP', 'character_exp')->class('form-label') }}
                            {{ html()->input('number', 'character_exp', request()->get('character_exp') ?? null)->class('form-control') }}
                        </div>

                        <div class="w-100"></div>

                        <div class="col-md mb-3">
                            {{ html()->label('Level of Highest Class/Job', 'character_highest')->class('form-label') }}
                            {{ html()->input('number', 'character_highest', request()->get('character_highest') ?? null)->class('form-control')->attribute('max', config('ffxiv.leveling_data.level_data.level_cap')) }}
                        </div>
                    </div>

                    <div class="mb-3">
                        {{ html()->checkbox('character_road', request()->get('character_road') ?? 0)->class('form-check-input') }}
                        {{ html()->label('"Road to 80" Buff', 'character_road')->class('form-check-label') }}
                    </div>

                    <h5>Gear</h5>
                    <div class="row">
                        <div class="col-md mb-3">
                            {{ html()->checkbox('gear_brand_new', request()->get('gear_brand_new') ?? 0)->class('form-check-input') }}
                            {{ html()->label('Brand New Ring', 'gear_brand_new')->class('form-check-label') }}
                        </div>

                        <div class="col-md mb-3">
                            {{ html()->checkbox('gear_earring', request()->get('gear_earring') ?? 0)->class('form-check-input') }}
                            {{ html()->label('Preorder Bonus Earring (Menphina\'s)', 'gear_earring')->class('form-check-label') }}
                        </div>
                    </div>

                    <h5>Temporary Buffs</h5>
                    <div class="row">
                        <div class="col-md mb-3">
                            {{ html()->label('FC EXP Buff (The Heat of Battle)', 'temp_fc')->class('form-label') }}
                            {{ html()->select('temp_fc', [1 => 'I', 2 => 'II', 3 => 'III'], request()->get('temp_fc') ?? null)->class('form-select')->placeholder('None/Select Level') }}
                        </div>

                        <div class="col-md-6 mb-3 mt-0 mt-md-4">
                            {{ html()->checkbox('temp_food', request()->get('temp_food') ?? 0)->class('form-check-input') }}
                            {{ html()->label('Food Buff', 'temp_food')->class('form-check-label') }}
                        </div>
                    </div>

                    <hr>

                    <p>
                        The calculated % bonus is {{ $bonus[1] }}% at 30 or below, {{ $bonus[31] }}% at
                        {{ config('ffxiv.leveling_data.level_data.level_cap') - 10 }} or below,
                        and {{ $bonus[config('ffxiv.leveling_data.level_data.level_cap') - 9] }}% at
                        {{ config('ffxiv.leveling_data.level_data.level_cap') - 9 }} and above, plus an additional 50% with
                        rested EXP when and where
                        applicable. If this does not match values observed in game, you may specify an override value here.
                        Note that the numbers provided here <i>do</i> adjust with this override. Also note that deep
                        dungeons and
                        Frontline do not use these numbers directly.
                    </p>

                    <div class="col-md mb-3">
                        {{ html()->label('Bonus % Override', 'override')->class('form-label') }}
                        {{ html()->input('number', 'override', request()->get('override') ?? null)->class('form-control') }}
                    </div>

                    <div class="ms-2 mb-3 text-end">
                        {{ html()->submit('Submit')->class('btn btn-primary') }}
                    </div>
                    {{ html()->form()->close() }}
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Tips" aria-expanded="true" aria-controls="Tips">
                    Leveling Tips
                </button>
            </h2>
            <div id="Tips" class="accordion-collapse collapse" data-bs-parent="#SettingsSelect">
                <div class="accordion-body">
                    <ul>
                        <li>
                            Do your dailies!
                            <ul>
                                <li>Roulettes (especially leveling and alliance raid, and MSQ if you've the patience)</li>
                                <li>Daily bonus Frontline (which gives a sizeable amount of EXP <i>for whichever class you
                                        queue as</i>, not necessarily what you <i>play</i> as within the match; just switch
                                    after loading in)</li>
                                <li>Tribe quests for your current level range/the relevant expansion (the EXP from these
                                    falls off very sharply outside of their relevant level range); these also benefit from
                                    rewarding more EXP when ranked up, and are a ready way to get some bonus EXP without too
                                    much investment</li>
                            </ul>
                        </li>
                        <li>
                            Leveling (non capstone/x0) dungeons are generally the most efficient repeatable source of EXP in
                            the game.
                            <ul>
                                <li>Keep in mind as well that if you're facing down long queue times or otherwise not up to
                                    playing a role/class with others, duty support covers the vast majority of leveling
                                    dungeons (as most are required for MSQ)! While it may be slower than with a party of
                                    other players, this doesn't account for time lost to queues-- or EXP lost to potentially
                                    not doing a dungeon at all.</li>
                                <li>Alternately, at level 71 and up, you can opt to use this time to level trusts if so
                                    desired (and make progress toward the relevant achievements)!</li>
                            </ul>
                        </li>
                        <li>FATEs generally aren't very efficient for large amounts of EXP, but are an easy way to get a
                            little extra, e.g. if very near a level at which a more efficient method becomes avaialble (and
                            provide additional rewards in ShB and later zones).</li>
                        <li>
                            Wondrous Tails provides 50% of a level once a week on turn-in, to the class active when it is
                            turned in!
                            <ul>
                                <li>To assist with completion, keep in mind that earlier (especially ARR) Extreme trials can
                                    be unsynced either with help or as a higher-level class.</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <p class="text-center">Up-to-date for 6.55. 7.0 updates will occur on getting sufficient data after release.</p>

    <div class="accordion" id="levelAccordion">
        @foreach (config('ffxiv.leveling_data.level_data.level_ranges') as $floor => $range)
            @if ((request()->get('character_level') && (request()->get('character_level') <= $range['ceiling'] || request()->get('character_level') == config('ffxiv.leveling_data.level_data.level_cap'))) || !request()->get('character_level'))
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ request()->get('character_level') && request()->get('character_level') >= $floor && request()->get('character_level') <= $range['ceiling'] ? '' : 'collapsed' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#range{{ $floor }}" aria-expanded="true" aria-controls="range{{ $floor }}">
                            {{ $floor }} to {{ $range['ceiling'] }}
                        </button>
                    </h2>
                    <div id="range{{ $floor }}" class="accordion-collapse collapse {{ request()->get('character_level') && request()->get('character_level') >= $floor && request()->get('character_level') <= $range['ceiling'] ? 'show' : '' }}"
                        data-bs-parent="#levelAccordion">
                        <div class="accordion-body">
                            <p>{!! $range['text'] !!}</p>

                            <p>Note that the number of runs given on each tab is how many are required to reach the end of
                                this level range.</p>

                            @include('leveling._data')
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection

@section('scripts')
    @parent

    <script type="module">
        $(document).ready(function() {
            var $useLodestone = $('#useLodestone');
            var $lodestoneContainer = $('#lodestoneContainer');
            var $manualContainer = $('#manualContainer');

            var useLodestone = $useLodestone.is(':checked');

            updateOptions();

            $useLodestone.on('change', function(e) {
                useLodestone = $useLodestone.is(':checked');

                updateOptions();
            });

            function updateOptions() {
                if (useLodestone) {
                    $lodestoneContainer.removeClass('d-none');
                    $manualContainer.addClass('d-none');
                } else {
                    $lodestoneContainer.addClass('d-none');
                    $manualContainer.removeClass('d-none');
                }
            }
        });
    </script>
@endsection

@section('credit')
    <p class="text-end">EXP values from:
        <a href="https://docs.google.com/spreadsheets/d/1CG0xtc_p4o3XzLQxK0A6tXz1koZF11JKN4yCLEGjrFo/edit?usp=sharing">PotD</a>,
        <a href="https://docs.google.com/spreadsheets/d/1M4gteurUKCGrniPqESBDBbsPpvi8p0oC_V41ShJZJ-s/edit?usp=sharing">HoH</a>,
        <a href="https://docs.google.com/spreadsheets/d/1yJ9cr606u0WKDVzHFXoa_DPt-bCJTJfUUcwVtQSHxJI/edit?usp=sharing">Dungeons</a>,
        <a href="https://docs.google.com/spreadsheets/d/1IJl01GIUmrjEpfiVK0ZKqTR-ExTO38dSxG9QIIIBxbk/edit?usp=sharing">own
            data (EO, Frontline)</a><br />
        Inspired by <a href="https://docs.google.com/spreadsheets/d/1OwfD4w0KMkvaUK-l5-piquWrjYKQDnorTZ_nMWCeCp4/edit?usp=sharing">Deep
            Dungeon Runs Calculator</a>
    </p>
@endsection
