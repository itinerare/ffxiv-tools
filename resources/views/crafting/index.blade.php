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
                    <button class="accordion-button {{ $paginator ? 'collapsed' : '' }} bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#craftingSettings" aria-expanded="true" aria-controls="craftingSettings">
                        Settings
                    </button>
                </h2>
                <div id="craftingSettings" class="accordion-collapse collapse {{ $paginator ? '' : 'show' }}" data-bs-parent="#craftingSettingsContainer">
                    <div class="accordion-body">
                        <p>Note that data is saved to your device as a cookie for convenience. Alternately, you can save the URL after submitting your settings.</p>

                        {{ html()->form('GET')->open() }}
                        {{ html()->hidden('world', request()->get('world') ?? null) }}

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    {{ html()->label('Job', 'character_job')->class('form-label') }}
                                    {{ html()->select('character_job', config('ffxiv.crafting.jobs'), request()->get('character_job') ?? null)->class('form-select')->placeholder('Select Job') }}
                                </div>

                                <div class="mt-0 mt-md-4 mb-3">
                                    {{ html()->checkbox('no_master', request()->get('no_master') ?? 0)->class('form-check-input') }}
                                    {{ html()->label('Don\'t include master recipes', 'no_master')->class('form-check-label') }}
                                </div>

                                <div class="mb-3">
                                    {{ html()->label('Minimum profit per (impacts recommendations)', 'min_profit')->class('form-label') }}
                                    {{ html()->number('min_profit', request()->get('min_profit') ?? null)->class('form-control') }}
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
                                            {{ html()->checkbox('include_aethersands', request()->get('include_aethersands') ?? 0)->class('form-check-input') }}
                                            {{ html()->label('Purchase aethersands', 'include_aethersands')->class('form-check-label') }}
                                        </div>

                                        <div class="mb-3">
                                            {{ html()->checkbox('purchase_drops', request()->get('purchase_drops') ?? 0)->class('form-check-input') }}
                                            {{ html()->label('Purchase monster drops', 'purchase_drops')->class('form-check-label') }}
                                        </div>
                                    </div>

                                    <div class="mw-100"></div>

                                    <div class="col-md mb-3">
                                        {{ html()->label('Gatherable Preference (MIN/BTN)', 'gatherable_preference')->class('form-label') }}
                                        {{ html()->select('gatherable_preference', [0 => 'Gather nothing/purchase all gatherables', 1 => 'Gather unrestricted (no perception requirement) materials', 2 => 'Gather all materials'], request()->get('gatherable_preference') ?? 0)->class('form-select') }}
                                    </div>

                                    <div class="col-md mb-3">
                                        {{ html()->label('Fish Preference', 'fish_preference')->class('form-label') }}
                                        {{ html()->select('fish_preference', [0 => 'Gather nothing/purchase all fish', 1 => 'Gather unrestricted (no folklore requirement) fish', 2 => 'Gather all fish'], request()->get('fish_preference') ?? 0)->class('form-select') }}
                                    </div>

                                    <div class="mw-100"></div>

                                    <div class="col-md mb-3">
                                        {{ html()->label('Vendor Item Preference', 'shop_preference')->class('form-label') }}
                                        {{ html()->select('shop_preference', [0 => 'Purchase all items from marketboard', 1 => 'Purchase items from vendors for gil', 2 => 'Purchase all possible materials from vendors'], request()->get('shop_preference') ?? 0)->class('form-select') }}
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

        @if ($paginator)
            <h3 class="text-center">Showing {{ config('ffxiv.crafting.jobs')[request()->get('character_job')] }} results for {{ ucfirst(request()->get('world')) }}</h3>
            @include('_universalis_note')
            <p class="text-center">
                Recommendations are made on the basis of trade velocity, profit margin (given the currently selected settings), and data recency.
            </p>

            {{ $paginator->links('crafting.pagination', ['itemName' => 'recipe']) }}

            <div class="card bg-light-subtle border-0 mb-4">
                <div class="card-body">
                    <h4>Recommended Recipes:</h4>
                    @if ($rankedRecipes->count())
                        <div class="row">
                            @foreach ($rankedRecipes as $recipe)
                                <div class="col-md-3 text-center">
                                    <h5>
                                        <small>{{ $loop->iteration }}.</small>
                                        <a href="#recipe-{{ $recipe->item_id }}">{{ $recipe->gameItem?->name }}</a>
                                    </h5>
                                    Profit Per: {!! $recipe->displayProfitPer($ingredients, $settings) !!}<br />
                                    <small>
                                        Sales per day:
                                        @if ($recipe->can_hq)
                                            {{ isset($recipe->priceData->first()->hq_sale_velocity) ? number_format($recipe->priceData->first()->hq_sale_velocity) : '(No Data)' }} HQ /
                                        @endif
                                        {{ isset($recipe->priceData->first()->nq_sale_velocity) ? number_format($recipe->priceData->first()->nq_sale_velocity) : '(No Data)' }}{{ $recipe->can_hq ? ' NQ' : '' }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center">None!{{ request()->get('min_profit') && request()->get('min_profit') > 0 ? ' Consider adjusting your settings. Alternately,' : '' }} Universalis may be experiencing difficulties.</p>
                    @endif
                </div>
            </div>

            @foreach ($paginator->items() as $recipe)
                <div class="card bg-dark-subtle border-0 {{ !$loop->last ? 'mb-2' : 'mb-3' }}">
                    <div class="card-body">
                        <div id="recipe-{{ $recipe->item_id }}" class="recipe-body mb-2">
                            <h4 class="mb-3">
                                @if ($recipe->gameItem)
                                    <div class="float-end text-end ms-1">
                                        <a href="{{ $recipe->gameItem->universalisUrl }}" class="btn btn-secondary btn-sm">Universalis</a>
                                        <a href="{{ $recipe->gameItem->teamcraftUrl }}" class="btn btn-secondary btn-sm">Teamcraft</a>
                                    </div>
                                @endif
                                {{ $recipe->gameItem->name ?? 'Unknown Item' }}
                                <small class="text-muted">
                                    ・ @include('crafting._recipe_info_display')
                                </small>
                            </h4>
                            <div class="row">
                                <div class="col-md-5">
                                    <p>
                                        @include('_item_price_display', ['priceData' => $recipe->priceData->first(), 'displayHQ' => $recipe->can_hq])
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
                                                        @foreach ($recipe->formatIngredients($ingredients, $settings) as $ingredientId => $ingredient)
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
                                                        @endif
                                                        @if ($recipe->calculateCostPer($ingredients, $settings, $quantity) >= 0)
                                                            @if ($recipe->yield > 1)
                                                                {{ number_format($recipe->calculateCostPer($ingredients, $settings, $quantity)) }} total,
                                                            @endif
                                                            {{ number_format(ceil($recipe->calculateCostPer($ingredients, $settings, $quantity) / $recipe->yield / $quantity)) }} Gil per
                                                        @else
                                                            (No Data)
                                                        @endif
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
                                                        {!! $recipe->displayProfitPer($ingredients, $settings, $quantity) !!}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{ $paginator->links('crafting.pagination', ['itemName' => 'recipe']) }}
        @endif
    @else
        <h1 class="text-center">Please select a world!</h1>
    @endif
@endsection

@section('credit')
    <p class="text-end">
        Market data from <a href="https://universalis.app">Universalis</a> ・ Game data from <a href="https://github.com/ffxiv-teamcraft/ffxiv-teamcraft">Teamcraft</a>
    </p>
@endsection
