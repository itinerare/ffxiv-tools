@if (isset($displayHQ) && $displayHQ)
    {{ isset($priceData->min_price_hq) ? number_format($priceData->min_price_hq) : '???' }} <small>(HQ)</small> /
    {{ isset($priceData->min_price_nq) ? number_format($priceData->min_price_nq) : '???' }} <small>(NQ)</small>
@else
    {{ isset($priceData->min_price_nq) ? number_format($priceData->min_price_nq) : '???' }}
@endif
Gil<br />
<small class="text-muted">
    Sales per day: {{ isset($priceData->nq_sale_velocity) ? number_format($priceData->nq_sale_velocity) : '(No Data)' }}
    @if (isset($displayHQ) && $displayHQ)
        NQ / {{ isset($priceData->hq_sale_velocity) ? number_format($priceData->hq_sale_velocity) : '(No Data)' }} HQ
    @endif
    ・
    @if ($priceData?->uploadTime)
        Last updated: {!! $priceData->uploadTime !!} ・
    @endif
    Last retrieved: {!! $priceData?->updatedTime !!}
</small>
