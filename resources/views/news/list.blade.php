<div class="news-list">
    @forelse($posts as $post)
        <article class="news-item">
            <div class="news-date"><span>{{ $post->created_at->format('d') }}</span><small>{{ $post->created_at->locale('th')->translatedFormat('M') }} {{ $post->created_at->year + 543 }}</small></div>
            <div class="news-item-content"><span class="badge">{{ $post->category->name }}</span><h3><a href="{{ route('news.show', $post) }}">{{ $post->title }}</a></h3><p>{{ \Illuminate\Support\Str::limit($post->body, 130) }}</p>@if($post->attachments_count)<small>ไฟล์แนบ {{ $post->attachments_count }} รายการ</small>@endif</div>
            <a class="news-arrow" href="{{ route('news.show', $post) }}" aria-label="อ่านข่าว {{ $post->title }}">↗</a>
        </article>
    @empty
        <div class="empty-state"><span class="icon" aria-hidden="true">☷</span><h3>ยังไม่มีข่าวที่เผยแพร่ในขณะนี้</h3><p>ข่าวสารและเอกสารของโรงพยาบาลจะแสดงที่นี่เมื่อเจ้าหน้าที่เผยแพร่</p></div>
    @endforelse
</div>