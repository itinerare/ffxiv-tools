@extends('layouts.app')

@section('content')
    <p class="text-center">
        Please select one of the tools from the navigation above!
    </p>

    <div class="accordion mb-3" id="changelogContainer">
        <div class="accordion-item bg-light-subtle border-0">
            <h2 class="accordion-header">
                <button class="accordion-button bg-light-subtle" type="button" data-bs-toggle="collapse" data-bs-target="#changelog" aria-expanded="true" aria-controls="changelog">
                    Changelog
                </button>
            </h2>
            <div id="changelog" class="accordion-collapse collapse" data-bs-parent="#changelogContainer">
                <div class="accordion-body">
                    @foreach ($changelog as $entry)
                        {!! $entry !!}
                    @endforeach
                    <div class="text-end h5">
                        <a href="https://code.itinerare.net/itinerare/ffxiv-tools/src/tag/v{{ config('version.tag') }}/CHANGELOG.md">See more...</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
