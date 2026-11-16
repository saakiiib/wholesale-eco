@php
    $alreadyVisited = in_array($navNode->id, $visitedCategoryIds, true);
    $nodeChildren = $alreadyVisited || $depth >= 8 ? collect() : $navCategories->where('parent_id', $navNode->id);
    $nodeActive = (int)($selectedCategoryId ?? 0) === (int)$navNode->id;
    $nodeInTrail = in_array($navNode->id, $ancestorCategoryIds ?? [], true);
    $expanded = $nodeActive || $nodeInTrail;
    $nextVisitedIds = [...$visitedCategoryIds, $navNode->id];
@endphp
@if(!$alreadyVisited)
    <div class="we-category-node" style="--category-depth: {{ min($depth, 8) }}">
        <div class="we-category-node-heading {{ $nodeActive ? 'is-active' : '' }} {{ $nodeInTrail ? 'is-ancestor' : '' }}">
            @if($nodeChildren->isNotEmpty())
                <button type="button" class="we-category-expand" aria-label="Toggle {{$navNode->name}} subcategories" aria-expanded="{{ $expanded ? 'true' : 'false' }}" aria-controls="we-category-children-{{$navNode->id}}" data-category-toggle> <span aria-hidden="true">▸</span> </button>
            @else
                <span class="we-category-leaf" aria-hidden="true">•</span>
            @endif
            <a href="{{ route('catalogue', ['category'=>$navNode->slug]) }}" @if($nodeActive) aria-current="page" @endif>{{ $navNode->name }}</a>
        </div>
        @if($nodeChildren->isNotEmpty())
            <div id="we-category-children-{{$navNode->id}}" class="we-category-children" @if(!$expanded) hidden @endif>
                @foreach($nodeChildren as $navChild)
                    @include('shop.partials.category-tree-node', [
                        'navNode' => $navChild,
                        'navCategories' => $navCategories,
                        'selectedCategoryId' => $selectedCategoryId,
                        'ancestorCategoryIds' => $ancestorCategoryIds,
                        'depth' => $depth + 1,
                        'visitedCategoryIds' => $nextVisitedIds
                    ])
                @endforeach
            </div>
        @endif
    </div>
@endif
