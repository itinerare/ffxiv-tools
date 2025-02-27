@extends('layouts.app')

@section('title')
    ・ Gathering Profit Calculator
@endsection

@section('meta-desc')
    Gathering profit calculator for Final Fantasy XIV. Offers recommendations for profitable items to gather, with configurable settings.
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
                                    {{ html()->label('Minimum price per (impacts recommendations)', 'min_price')->class('form-label') }}
                                    {{ html()->number('min_price', request()->get('min_price') ?? null)->class('form-control') }}
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="row">
                                    <div class="col-md">
                                        <div class="row">
                                            <div class="col-md mt-0 mt-md-4 mb-3">
                                                {{ html()->checkbox('include_limited', request()->get('include_limited') ?? 0)->class('form-check-input') }}
                                                {{ html()->label('Include limited (perception requirement) nodes', 'include_limited')->class('form-check-label') }}
                                            </div>

                                            <div class="col-md mt-0 mt-md-4 mb-3">
                                                {{ html()->checkbox('include_aethersands', request()->get('include_aethersands') ?? 0)->class('form-check-input') }}
                                                {{ html()->label('Include aethersands', 'include_aethersands')->class('form-check-label') }}
                                            </div>

                                            <div class="mw-100"></div>

                                            <div class="col-md mb-3">
                                                {{ html()->label('Fish Preference', 'fish_preference')->class('form-label') }}
                                                {{ html()->select('fish_preference', [0 => 'Do not include fish', 1 => 'Include unrestricted (no folklore requirement) fish', 2 => 'Include all fish'], request()->get('fish_preference') ?? 0)->class('form-select') }}
                                            </div>
                                        </div>
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
            <h3 class="text-center">Showing results for {{ ucfirst(request()->get('world')) }}</h3>
            @include('_universalis_note')
            <p class="text-center">
                The items listed here correspond to items presently used in the corresponding level range's crafting recipes, and which are consequently liable to be in demand. Recommendations are made on the basis of trade velocity, price, and data
                recency, while the complete list is ordered by price alone.
            </p>

            <p class="text-center">
                Items listed are updated periodically based on Teamcraft's data and (in theory) should always be up-to-date.
            </p>

            {{ $paginator->links('crafting.pagination', ['itemName' => 'item']) }}

            <div class="card bg-light-subtle border-0 mb-4">
                <div class="card-body">
                    <h4>Recommended Items:</h4>
                    <div class="row">
                        @foreach ($rankedItems as $itemId => $item)
                            <div class="col-md-3 text-center mb-2">
                                <h6>
                                    <small>{{ $loop->iteration }}.</small>
                                    <a href="#item-{{ $itemId }}">{{ $item['gameItem']?->name }}</a>
                                </h6>
                                <small>
                                    {{ number_format($item['priceData']?->min_price_nq) }} Gil per ・
                                    Sales per day:
                                    {{ isset($item['priceData']?->nq_sale_velocity) ? number_format($item['priceData']?->nq_sale_velocity) : '(No Data)' }}
                                </small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card bg-dark-subtle border-0 mb-3">
                <div class="card-body">
                    <div class="row">
                        @foreach ($paginator->items() as $itemId => $item)
                            {!! $loop->first || $loop->iteration == ceil($paginator->count() / 2) + 1 ? '<div class="col-md"><ol start="' . $loop->iteration . '">' : '' !!}
                            <li id="item-{{ $itemId }}">
                                @if ($item['gameItem'] ?? false)
                                    <span class="float-end text-end">
                                        <a href="{{ $item['gameItem']?->universalisUrl }}" class="btn btn-secondary btn-sm py-0">Universalis</a>
                                        <a href="{{ $item['gameItem']?->teamcraftUrl }}" class="btn btn-secondary btn-sm py-0">Teamcraft</a>
                                    </span>
                                @endif
                                {{ $item['gameItem']?->name ?? 'Unknown Item' }} - @include('_item_price_display', ['priceData' => $item['priceData']])
                            </li>
                            {!! $loop->last || $loop->iteration == ceil($paginator->count() / 2) ? '</ol></div>' : '' !!}
                        @endforeach
                    </div>
                </div>
            </div>

            {{ $paginator->links('crafting.pagination', ['itemName' => 'item']) }}
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
