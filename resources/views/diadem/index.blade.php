@extends('layouts.app')

@section('title')
    ・ Diadem Gil Optimization
@endsection

@section('content')
    @include('_data_center_select', ['world' => request()->get('world') ?? null])
    @if (request()->get('world') && $items)
        <h3 class="text-center">Showing Results for {{ ucfirst(request()->get('world')) }}</h3>
        <p class="text-center">Results may be updated at most every 6 hours; updates are queued on viewing data for a world and may take a minute or two to be fetched (provided Universalis is currently healthy).</p>
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
                                    <strong>{{ $name }}</strong> - {{ isset($item->min_price_nq) ? number_format($item->min_price_nq) : '???' }} Gil<br />
                                    <small class="text-muted">
                                        Sales per day: {{ isset($item->nq_sale_velocity) ? number_format($item->nq_sale_velocity) : '(No Data)' }} ・
                                        @if ($item->uploadTime)
                                            Last updated: {!! $item->uploadTime !!} ・
                                        @endif
                                        Last retrieved: {!! $item->updatedTime !!}
                                    </small>
                                </li>
                            @endforeach
                        </ol>
                        @foreach ($chunk as $node)
                            <div class="alert alert-{{ $class == 'BTN' ? 'success' : 'info' }}">
                                <ul>
                                    @foreach ($node as $name => $item)
                                        <li>
                                            <strong>{{ $name }}</strong> - {{ isset($item->min_price_nq) ? number_format($item->min_price_nq) : '???' }} Gil<br />
                                            <small class="text-muted">
                                                Sales per day: {{ isset($item->nq_sale_velocity) ? number_format($item->nq_sale_velocity) : '(No Data)' }} ・
                                                @if ($item->uploadTime)
                                                    Last updated: {!! $item->uploadTime !!} ・
                                                @endif
                                                Last retrieved: {!! $item->updatedTime !!}
                                            </small>
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
    <p>Data from <a href="https://universalis.app">Universalis</a></p>
@endsection
