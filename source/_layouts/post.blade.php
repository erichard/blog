@extends('_layouts.master')

@section('meta')
    <meta property="og:title" content="{{ $page->title ?  $page->title . ' | ' : '' }}{{ $page->siteName }}"/>
    <meta property="og:url" content="{{ $page->getUrl() }}"/>
    <meta property="og:type" content="article" />
    <meta property="og:description" content="{{ $page->excerpt }}" />
    @if ($page->meta_image)
        <meta property="og:image" content="{{ $page->baseUrl }}{{ $page->meta_image }}" />
    @endif
@endsection

@section('body')
    @if ($page->cover_image)
        <img src="{{ $page->cover_image }}" alt="{{ $page->title }} cover image" class="mb-2 mx-auto" style="height: 350px">
    @endif

    <h1 class="leading-none mb-2">{{ $page->title }}</h1>

    <p class="text-gray-700 text-xl md:mt-0">{{ $page->getDate()->isoFormat('LL') }}</p>

    <div class="border-b border-blue-200 mb-10 pb-4" v-pre>
        @yield('content')
    </div>

    <nav class="flex justify-between text-sm md:text-base">
        <div>
            @if ($next = $page->getNext())
                <a href="{{ $next->getUrl() }}" title="Older Post: {{ $next->title }}">
                    &LeftArrow; {{ $next->title }}
                </a>
            @endif
        </div>

        <div>
            @if ($previous = $page->getPrevious())
                <a href="{{ $previous->getUrl() }}" title="Newer Post: {{ $previous->title }}">
                    {{ $previous->title }} &RightArrow;
                </a>
            @endif
        </div>
    </nav>

    @if ($page->ghcommentid)
         @include('_components.comments')
    @endif

@endsection
