@if (isset($displayHQ) && $displayHQ)
    {{ isset($priceData->min_price_hq) ? number_format($priceData->min_price_hq) : '???' }} <small>(HQ)</small> /
    {{ isset($priceData->min_price_nq) ? number_format($priceData->min_price_nq) : '???' }} <small>(NQ)</small>
@else
    {{ isset($priceData->min_price_nq) ? number_format($priceData->min_price_nq) : '???' }}
@endif
Gil<br />
<small class="text-muted">
    Sales per day:
    @if (isset($displayHQ) && $displayHQ)
        {{ isset($priceData->hq_sale_velocity) ? number_format($priceData->hq_sale_velocity) : '(No Data)' }} HQ /
    @endif
    {{ isset($priceData->nq_sale_velocity) ? number_format($priceData->nq_sale_velocity) : '(No Data)' }}{{ isset($displayHQ) && $displayHQ ? ' NQ' : '' }}
    ・
    @if ($priceData?->uploadTime)
        Last updated: {!! $priceData->uploadTime !!} ・
    @endif
    Last retrieved: {!! $priceData->updatedTime ?? 'Never' !!}
</small>
