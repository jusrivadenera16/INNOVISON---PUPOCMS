@if($paginator->hasPages())
    <nav class="evaluation-pager" aria-label="Submitted responses pagination">
        <p class="evaluation-pager-summary">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</p>
        <div class="evaluation-pager-actions">
            @if($paginator->onFirstPage())
                <span class="evaluation-pager-btn is-disabled" aria-disabled="true" aria-label="Previous page">&larr;</span>
            @else
                <a class="evaluation-pager-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&larr;</a>
            @endif
            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="evaluation-pager-gap">{{ $element }}</span>
                @else
                    @foreach($element as $page => $url)
                        @if($page === $paginator->currentPage())
                            <span class="evaluation-pager-btn is-active" aria-current="page" aria-label="Page {{ $page }}">{{ $page }}</span>
                        @else
                            <a class="evaluation-pager-btn" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
            @if($paginator->hasMorePages())
                <a class="evaluation-pager-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&rarr;</a>
            @else
                <span class="evaluation-pager-btn is-disabled" aria-disabled="true" aria-label="Next page">&rarr;</span>
            @endif
        </div>
    </nav>
@endif
