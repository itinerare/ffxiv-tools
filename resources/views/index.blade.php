@extends('layouts.app')

@section('content')
    <p class="text-center">
        Please select one of the tools from the navigation above!
    </p>

    <p class="text-center">
        If you encounter an issue while using the site, please create an issue on <a href="https://github.com/itinerare/ffxiv-tools/issues">GitHub</a> (preferred) or send me an email at <code>ffxiv [at] itinerare.net</code>.
    </p>

    <p class="text-center">
        Additionally, if any of the utilities here are useful to you, consider donating via Ko-fi!<br />
        <a href='https://ko-fi.com/A86644L3' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://storage.ko-fi.com/cdn/kofi5.png?v=6' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>
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
