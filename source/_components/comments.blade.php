<h3>Bavardage</h3>

@forelse ($page->getComments() as $comment) 
<div class="comment flex py-4 @if (!$loop->last) border-b @endif">
    <div class="w-1/6 text-center text-gray-500">
        <img class="h-12 w-12 rounded-full mx-auto shadow-inner" src="{{ $comment->user->avatar_url }}">
        {{ $comment->user->login }}
        <p class="text-sm">{{ $page->getCommentDate($comment)->isoFormat('LL') }}</p>
    </div>
    <div class="w-5/6 px-4 text-gray-700 comment-html">
        {!! $comment->body_html !!}
    </div>
</div>
<div class="text-center">
Venez discuter sur <a href="{{ $page->getCommentUrl() }}">Github</a> !
</div>

@empty

<div class="text-center">
	Soyez le premier à commenter et venez sur <a href="{{ $page->getCommentUrl() }}">Github</a> !

	<img src="/assets/img/undraw_public_discussion_btnw.svg" class="mx-auto w-64 mt-4">
</div>

@endforelse
