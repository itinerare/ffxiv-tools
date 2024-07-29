{{ $recipe->calculateProfitPer(request()->get('world'), 1, $settings) ? number_format($recipe->calculateProfitPer(request()->get('world'), 1, $settings, $quantity ?? 1)) : '???' }}
<small>(HQ)</small> /
{{ $recipe->calculateProfitPer(request()->get('world'), 0, $settings) ? number_format($recipe->calculateProfitPer(request()->get('world'), 0, $settings, $quantity ?? 1)) : '???' }}
<small>(NQ)</small> Gil
