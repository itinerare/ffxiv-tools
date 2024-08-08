<p class="text-center">
    Local data may be updated from Universalis every {{ config('ffxiv.universalis.cache_lifetime') }} minutes at most. Both update (last upload to Universalis) and retrieval (local data last updated) times are shown, as available.<br />
    Updates are queued on viewing data for a world and may take a minute or two to be fetched (provided Universalis is currently healthy).
</p>

@if (isset($universalisUpdate) and $universalisUpdate)
    <meta http-equiv="refresh" content="120">
@endif
