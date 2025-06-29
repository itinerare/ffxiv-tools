@php
    if (count(request()->all())) {
        $settingsString = '';
        foreach (request()->all() as $key => $setting) {
            if ($key != 'character_job') {
                $settingsString = $settingsString . '&' . $key . '=' . $setting;
            }
        }
    }
@endphp

<div class="text-center mb-3">
    {{ html()->form('GET')->open() }}
        @if (request()->get('character_job'))
            <img src="{{ asset('images/classjob/'.request()->get('character_job').'.png') }}" data-bs-toggle="tooltip" data-toggle="tooltip" title="Currently selected job ({{ $jobs[request()->get('character_job')] }})" /> ・
        @endif
        @foreach ($jobs as $jobId => $job)
            @if (!request()->get('character_job') || $jobId != request()->get('character_job'))
                <a href="?character_job={{ $jobId }}{{ $settingsString ?? '' }}"><img src="{{ asset('images/classjob/'.$jobId.'.png') }}" data-bs-toggle="tooltip" data-toggle="tooltip" title="{{ $job }}" /></a>
            @endif
        @endforeach
    {{ html()->form()->close() }}
</div>
