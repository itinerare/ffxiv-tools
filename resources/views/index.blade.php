@extends('layouts.app')

@section('content')
    <div class="text-center mb-4">
        @foreach($dataCenters as $dataCenter=>$servers)
        <div class="mb-2">
            <span class="h5">{{ $dataCenter }}:</span>
            @foreach($servers as $server)
                <a href="?world={{ strtolower($server) }}" class="btn {{ $world == strtolower($server) ? 'btn-success' : 'btn-primary' }} py-0">{{ $server }}</a>
            @endforeach
        </div>
        @endforeach
    </div>
    @if($world)
        <h3 class="text-center">Showing Results for {{ ucfirst($world) }}</h3>
        <div class="card-group">
            @foreach($items as $class=>$chunk)
                <div class="card">
                    <div class="card-header text-center"><h5>{{ $class }}</h5></div>
                    <div class="card-body">
                        <h5>Top Five:</h5>
                        <ol>
                            @foreach($rankedItems[$class]->take(5) as $item=>$price)
                                <li><strong>{{ $item }}</strong> - {{ $price }} Gil</li>
                            @endforeach
                        </ol>
                        @foreach($chunk as $node)
                            <div class="alert alert-{{ $class == 'BTN' ? 'success' : 'info' }}"><ul>
                                @foreach($node as $item=>$price)
                                    <li><strong>{{ $item }}</strong> - {{ $price }} Gil</li>
                                @endforeach
                            </ul></div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <h1 class="text-center">Please select a world!</h1>
    @endif
@endsection
