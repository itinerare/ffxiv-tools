@extends('layouts.app')

@section('title')
    ・ Crafting Profit Calculator
@endsection

@section('content')
    @include('_data_center_select', ['currentWorld' => $world ?? null])
    @if (request()->get('world'))
        <div class="accordion mb-3" id="craftingSettingsContainer">
            <div class="accordion-item bg-light-subtle border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button {{ $recipes ? 'collapsed' : '' }} bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#craftingSettings" aria-expanded="true" aria-controls="craftingSettings">
                        Settings
                    </button>
                </h2>
                <div id="craftingSettings" class="accordion-collapse collapse {{ $recipes ? '' : 'show' }}" data-bs-parent="#craftingSettingsContainer">
                    <div class="accordion-body">
                        {{ html()->form('GET')->open() }}
                        {{ html()->hidden('world', request()->get('world') ?? null) }}

                        <div class="row">
                            <div class="col-md-4">
                                <div class="col-md mb-3">
                                    {{ html()->label('Job', 'character_job')->class('form-label') }}
                                    {{ html()->select('character_job', config('ffxiv.crafting.jobs'), request()->get('character_job') ?? null)->class('form-select')->placeholder('Select Job') }}
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="row">
                                    <div class="col-md mt-0 mt-md-4">
                                        <div class="mb-3">
                                            {{ html()->checkbox('purchase_precrafts', request()->get('purchase_precrafts') ?? 0)->class('form-check-input') }}
                                            {{ html()->label('Purchase precrafts', 'purchase_precrafts')->class('form-check-label') }}
                                        </div>

                                        <div class="mb-3">
                                            {{ html()->checkbox('prefer_hq', request()->get('prefer_hq') ?? 0)->class('form-check-input') }}
                                            {{ html()->label('Prefer HQ materials', 'prefer_hq')->class('form-label') }}
                                        </div>
                                    </div>

                                    <div class="col-md mt-0 mt-md-4">
                                        <div class="mb-3">
                                            {{ html()->checkbox('include_crystals', request()->get('include_crystals') ?? 0)->class('form-check-input') }}
                                            {{ html()->label('Include crystal costs', 'include_crystals')->class('form-check-label') }}
                                        </div>

                                        <div class="mb-3">
                                            {{ html()->checkbox('purchase_drops', request()->get('purchase_drops') ?? 0)->class('form-check-input') }}
                                            {{ html()->label('Purchase monster drops', 'purchase_drops')->class('form-check-label') }}
                                        </div>
                                    </div>

                                    <div class="mw-100"></div>

                                    <div class="col-md mb-3">
                                        {{ html()->label('Gatherable Preference', 'gatherable_preference')->class('form-label') }}
                                        {{ html()->select('gatherable_preference', [0 => 'Gather nothing/purchase all gatherables', 1 => 'Gather unrestricted (no perception requirement) materials', 2 => 'Gather all materials'], request()->get('gatherable_preference') ?? 0)->class('form-select') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ms-2 mb-3 text-end">
                            {{ html()->submit('Submit')->class('btn btn-primary') }}
                        </div>
                        {{ html()->form()->close() }}
                    </div>
                </div>
            </div>
        </div>

        @if ($recipes)
            <h3 class="text-center">Showing {{ config('ffxiv.crafting.jobs')[request()->get('character_job')] }} results for {{ ucfirst(request()->get('world')) }}</h3>
            <p class="text-center">
                Local data may be updated from Universalis every 6 hours at most. Both update (last upload to Universalis) and retrieval (local data last updated) times are shown, as available.<br />
                Updates are queued on viewing data for a world and may take a minute or two to be fetched (provided Universalis is currently healthy).
            </p>

            <div class="accordion" id="craftingAccordion">
                @foreach (config('ffxiv.crafting.ranges') as $label => $range)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#range{{ $loop->iteration }}" aria-expanded="true" aria-controls="range{{ $loop->iteration }}">
                                {{ $label }}
                            </button>
                        </h2>
                        <div id="range{{ $loop->iteration }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#craftingAccordion">
                            <div class="accordion-body">
                                <h4>Top Recipes:</h4>
                                <div class="row">
                                    @foreach ($rankedRecipes[$label] as $recipe)
                                        <div class="col-md-3 text-center">
                                            <h5>
                                                <small>{{ $loop->iteration }}.</small>
                                                <a href="#recipe-{{ $recipe->item_id }}">{{ $recipe->gameItem?->name }}</a>
                                            </h5>
                                            Profit Per: {!! $recipe->displayProfitPer($ingredients[$label], 1, $settings) !!}<br />
                                            <small>
                                                Sales per day:
                                                {{ isset($recipe->priceData->first()->hq_sale_velocity) ? number_format($recipe->priceData->first()->hq_sale_velocity) : '(No Data)' }} HQ /
                                                {{ isset($recipe->priceData->first()->nq_sale_velocity) ? number_format($recipe->priceData->first()->nq_sale_velocity) : '(No Data)' }} NQ
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                                <hr />

                                @foreach ($recipes[$label] as $recipe)
                                    <div id="recipe-{{ $recipe->item_id }}" class="recipe-body mb-2">
                                        <h4 class="mb-3">
                                            {{ $recipe->gameItem?->name }}
                                            <small class="text-muted">
                                                ・ @include('crafting._recipe_info_display')
                                            </small>
                                            <div class="float-end text-end">
                                                <a href="https://ffxivteamcraft.com/db/en/item/{{ $recipe->item_id }}" class="btn btn-secondary btn-sm">Teamcraft</a>
                                            </div>
                                        </h4>
                                        <div class="row">
                                            <div class="col-md-5">
                                                <p>
                                                    @include('_item_price_display', ['priceData' => $recipe->priceData->first(), 'displayHQ' => true])
                                                </p>
                                                <div class="accordion" id="ingredients{{ $recipe->item_id }}">
                                                    <div class="accordion-item bg-light-subtle border-0">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#ingredients-{{ $recipe->id }}" aria-expanded="true"
                                                                aria-controls="ingredients-{{ $recipe->id }}">
                                                                Ingredients
                                                            </button>
                                                        </h2>
                                                        <div id="ingredients-{{ $recipe->id }}" class="accordion-collapse collapse" data-bs-parent="#ingredients{{ $recipe->item_id }}">
                                                            <div class="accordion-body">
                                                                <ul>
                                                                    @foreach ($recipe->formatIngredients($ingredients[$label]) as $ingredientId => $ingredient)
                                                                        @include('crafting._ingredient_display')
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md">
                                                <div class="row">
                                                    <div class="col-md">
                                                        Cost to make:
                                                        <ul>
                                                            @foreach ([1] + ($recipe->yield > 1 ? [5, 10, 33] : []) as $quantity)
                                                                <li>
                                                                    @if ($recipe->yield > 1)
                                                                        x{{ $quantity }} <small>(Makes {{ $quantity * $recipe->yield }})</small>:
                                                                        {{ number_format($recipe->calculateCostPer($ingredients[$label], $settings, $quantity)) }} total,
                                                                    @endif
                                                                    {{ number_format(ceil($recipe->calculateCostPer($ingredients[$label], $settings, $quantity) / $recipe->yield / $quantity)) }} Gil per
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="col-md">
                                                        Profit <small>(per individual item)</small>:
                                                        <ul>
                                                            @foreach ([1] + ($recipe->yield > 1 ? [5, 10, 33] : []) as $quantity)
                                                                <li>
                                                                    @if ($recipe->yield > 1)
                                                                        x{{ $quantity }}:
                                                                    @endif
                                                                    {!! $recipe->displayProfitPer($ingredients[$label], 1, $settings, $quantity) !!}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {!! !$loop->last ? '<hr />' : '' !!}
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <h1 class="text-center">Please select a world!</h1>
    @endif
@endsection

@section('credit')
    <p class="text-end">
        Market data from <a href="https://universalis.app">Universalis</a><br />
        Game data from <a href="https://xivapi.com">XIVAPI</a> and <a href="https://github.com/ffxiv-teamcraft/ffxiv-teamcraft">Teamcraft</a>
    </p>
@endsection
