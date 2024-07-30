<li>
    <strong>
        {{ $ingredient['amount'] }} {{ $ingredient['gameItem']->name ?? 'Unknown Item' }}
        @if ($ingredient['gameItem']?->shop_data)
            <span class="text-primary" data-bs-toggle="tooltip" data-toggle="tooltip"
                title="Available from a vendor{{ $ingredient['gameItem']->shop_data['currency'] == 1 ? ' for ' . number_format($ingredient['gameItem']->shop_data['cost']) . ' Gil per' : '' }}">*</span>
        @endif
    </strong> -
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
