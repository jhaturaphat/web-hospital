<nav class="category-nav" aria-label="หมวดหมู่ข่าวสาร">
    <a href="{{ route('news.index') }}" @if(!request('category')) aria-current="page" @endif>ทั้งหมด</a>
    @foreach($categories as $category)
        <a href="{{ route('news.index', ['category' => $category->id]) }}" @if((string)request('category') === (string)$category->id) aria-current="page" @endif>{{ $category->name }}</a>
    @endforeach
</nav>