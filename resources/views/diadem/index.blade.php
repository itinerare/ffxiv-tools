@extends('layouts.app')

@section('title')
    ・ Diadem Gil Optimization
@endsection

@section('content')
    @include('_data_center_select', ['world' => request()->get('world') ?? null])
    @if (request()->get('world') && $items)
        <h3 class="text-center">Showing Results for {{ ucfirst(request()->get('world')) }}</h3>
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
                                    <strong>{{ $name }}</strong> - {{ $item->price_per_unit ?? '???' }} Gil<br />
                                    <small class="text-muted">Sales per day: {{ $item->nq_sale_velocity ?? '(No Data)' }} ・ Last updated: {!! $item->updatedTime !!}</small>
                                </li>
                            @endforeach
                        </ol>
                        @foreach ($chunk as $node)
                            <div class="alert alert-{{ $class == 'BTN' ? 'success' : 'info' }}">
                                <ul>
                                    @foreach ($node as $name => $item)
                                        <li>
                                            <strong>{{ $name }}</strong> - {{ $item->price_per_unit ?? '???' }} Gil<br />
                                            <small class="text-muted">Sales per day: {{ $item->nq_sale_velocity ?? '(No Data)' }} ・ Last updated: {!! $item->updatedTime !!}</small>
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
