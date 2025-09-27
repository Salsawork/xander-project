@if ($paginator->hasPages())
    <div class="flex justify-center my-6 space-x-1">
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded cursor-not-allowed">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 text-gray-700 bg-white rounded hover:bg-gray-100">Previous</a>
        @endif

        @foreach ($paginator->links()->elements[0] as $page => $url)
            @if ($page == $paginator->currentPage())
                <span class="px-3 py-1 bg-blue-600 text-white rounded">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="px-3 py-1 bg-white text-gray-700 rounded hover:bg-gray-100">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 text-gray-700 bg-white rounded hover:bg-gray-100">Next</a>
        @else
            <span class="px-3 py-1 text-gray-500 bg-gray-200 rounded cursor-not-allowed">Next</span>
        @endif
    </div>
@endif
