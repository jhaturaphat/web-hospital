@extends('layouts.hospital')
@section('title', $post->title)
@section('content')
<a class="text-link" href="{{ route('news.index', ['category' => $post->news_category_id]) }}">← กลับรายการข่าว</a>
<article class="article-panel">
    <span class="badge">{{ $post->category->name }}</span>
    @unless($post->is_published)<div class="notice">ฉบับร่าง — มองเห็นได้เฉพาะเจ้าหน้าที่</div>@endunless
    <h1 class="page-title">{{ $post->title }}</h1>
    <p>วันที่ประกาศ {{ $post->created_at->format('d/m') }}/{{ $post->created_at->year + 543 }}</p>
    <div class="article-body">{{ $post->body }}</div>
    @if($post->attachments->isNotEmpty())
        <section class="attachments"><h2>เอกสารและไฟล์แนบ</h2><ul>@foreach($post->attachments as $attachment)<li><a href="{{ route('news.download', $attachment) }}">↓ {{ $attachment->name }}</a><small>{{ number_format($attachment->size / 1024) }} KB</small></li>@endforeach</ul></section>
    @endif
    @can('manage-news')<a class="button" href="{{ route('staff.posts.edit', $post) }}">แก้ไขข่าวนี้</a>@endcan
</article>
@endsection