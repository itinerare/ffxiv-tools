@if (isset($job) && $recipe->job != $job)
    {{ config('ffxiv.crafting.jobs')[$recipe->job] }} ・
@endif
Lvl {{ $recipe->level }}
@if ($recipe->stars)
    ・ @for ($i = 1; $i < $recipe->stars; $i++)
        ☆
    @endfor
@endif
・ Rarity Level {{ $recipe->rlvl }}
・ Yields {{ $recipe->yield }}
