<li>
    <strong>{{ $ingredient['amount'] }} {{ $ingredient['gameItem']->name ?? $ingredientId }}</strong> -
    @include('_item_price_display', ['priceData' => $ingredient['priceData'], 'displayHQ' => $ingredient['recipe'] ? $recipe->can_hq : false])
    @if ($ingredient['recipe'])
        <br />
        Recipe: @include('crafting._recipe_info_display', ['recipe' => $ingredient['recipe'], 'job' => request()->get('character_job')])
        <ul>
            @foreach ($ingredient['recipe']->formatIngredients($ingredients) as $ingredient)
                @include('crafting._ingredient_display')
            @endforeach
        </ul>
    @endif
</li>
