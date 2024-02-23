@php
    if (isset($world)) {
        foreach (config('ffxiv.data_centers') as $region => $dataCenters) {
            foreach ($dataCenters as $dataCenter) {
                if (in_array(ucfirst($world), $dataCenter)) {
                    $currentRegion = $region;
                    break;
                }
            }
        }
    }
@endphp

<div class="accordion mb-4" id="dataCenterSelect">
    @foreach (config('ffxiv.data_centers') as $region => $dataCenters)
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button {{ $world ? ($currentRegion == $region ? '' : 'collapsed') : ($loop->first ? '' : 'collapsed') }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ str_replace(' ', '_', $region) }}"
                    aria-expanded="true" aria-controls="collapseOne">
                    {{ $region }}
                </button>
            </h2>
            <div id="{{ str_replace(' ', '_', $region) }}" class="accordion-collapse collapse {{ $world ? ($currentRegion == $region ? 'show' : '') : ($loop->first ? 'show' : '') }}" data-bs-parent="#dataCenterSelect">
                <div class="accordion-body">
                    @foreach ($dataCenters as $dataCenter => $servers)
                        <div class="mb-2">
                            <span class="h5">{{ $dataCenter }}:</span>
                            @foreach ($servers as $server)
                                <a href="?world={{ strtolower($server) }}" class="btn {{ $world == strtolower($server) ? 'btn-success' : 'btn-primary' }} py-0 my-1 my-md-0">{{ $server }}</a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
