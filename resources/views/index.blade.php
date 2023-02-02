@extends('layouts.app')

@section('content')
    <div class="card-group">
        @foreach($pricedItems as $class=>$chunk)
            <div class="card">
                <div class="card-header text-center"><h5>{{ $class }}</h5></div>
                <div class="card-body">
                    <ol>
                        @foreach($chunk as $item=>$price)
                            <li><strong>{{ $item }}</strong> - {{ $price }} gil</li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endforeach
    </div>
@endsection
