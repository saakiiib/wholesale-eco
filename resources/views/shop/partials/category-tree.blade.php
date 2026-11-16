@php
    // Render parent categories once; recurse through active children in configured display order.
    $treeRoots = $navCategories->filter(fn($c) => !$c->parent_id || !$navCategories->contains('id', $c->parent_id));
@endphp
<nav class="we-category-tree" aria-label="Browse product categories">
    @foreach($treeRoots as $navNode)
        @include('shop.partials.category-tree-node', [
            'navNode' => $navNode,
            'navCategories' => $navCategories,
            'selectedCategoryId' => $selectedCategoryId ?? null,
            'ancestorCategoryIds' => $ancestorCategoryIds ?? [],
            'depth' => 0,
            'visitedCategoryIds' => []
        ])
    @endforeach
</nav>
