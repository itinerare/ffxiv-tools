@if (isset($job) && $recipe->job != $job)
    {{ config('ffxiv.crafting.jobs')[$recipe->job] }} ・
@endif
Lvl {{ $recipe->level }}
@if ($recipe->stars)
    ・ @for ($i = 0; $i < $recipe->stars; $i++)
        ☆
    @endfor
@endif
・ Rarity Level {{ $recipe->rlvl }}
・ Yields {{ $recipe->yield }}
・ {{ $recipe->can_hq ? 'Can' : 'No' }} HQ
