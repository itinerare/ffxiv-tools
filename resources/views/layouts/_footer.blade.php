<div class="copyright">
    @hasSection('credit')
        <div class="float-end align-self-center">
            @yield('credit')
        </div>
    @endif
    <p>
        <a href="https://code.itinerare.net/itinerare/ffxiv-tools">FFXIV Tools</a> {{ config('version.string') }} ・
        <a href="https://code.itinerare.net/itinerare/ffxiv-tools/src/branch/main/CHANGELOG.md">Changelog</a>
    </p>
</div>
