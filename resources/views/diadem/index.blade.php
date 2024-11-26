@extends('layouts.app')

@section('title')
    ・ Diadem Gil Optimization
@endsection

@section('content')
    @include('_data_center_select', ['world' => request()->get('world') ?? null])
    @if (request()->get('world') && $items)
        <h3 class="text-center">Showing Results for {{ ucfirst(request()->get('world')) }}</h3>
        @include('_universalis_note')
        <p class="text-center">
            "Top Five" items are listed on the basis of trade velocity, price, and data recency.
        </p>

        <div class="card-group">
            @foreach ($items as $class => $chunk)
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $class }}</h5>
                    </div>
                    <div class="card-body">
                        <h5>Top Five:</h5>
                        <ol>
                            @foreach ($rankedItems[$class] as $name => $item)
                                <li>
                                    @if ($item->gameItem)
                                        <div class="float-end text-end">
                                            <a href="{{ $item->gameItem->universalisUrl }}" class="btn btn-secondary btn-sm py-0">Universalis</a>
                                        </div>
                                    @endif
                                    <strong>{{ $name }}</strong> -
                                    @include('_item_price_display', ['priceData' => $item])
                                </li>
                            @endforeach
                        </ol>
                        @foreach ($chunk as $node)
                            <div class="alert alert-{{ $class == 'BTN' ? 'success' : 'info' }}">
                                <ul>
                                    @foreach ($node as $name => $item)
                                        <li>
                                            @if ($item->gameItem)
                                                <div class="float-end text-end">
                                                    <a href="{{ $item->gameItem->universalisUrl }}" class="btn btn-secondary btn-sm py-0">Universalis</a>
                                                </div>
                                            @endif
                                            <strong>{{ $name }}</strong> -
                                            @include('_item_price_display', ['priceData' => $item])
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @elseif (request()->get('world'))
        <h1 class="text-center">Item data for {{ ucfirst(request()->get('world')) }} is still being initialized.<br /> Please try again later.</h1>
    @else
        <h1 class="text-center">Please select a world!</h1>
    @endif
@endsection

@section('credit')
    <p class="text-end">
        Market data from <a href="https://universalis.app">Universalis</a> ・ Game data from <a href="https://github.com/ffxiv-teamcraft/ffxiv-teamcraft">Teamcraft</a>
    </p>
@endsection
