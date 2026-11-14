@extends('layouts.hospital')
@section('title', 'ข่าวสารและประกาศ')
@section('content')
<div class="section-heading"><div><p class="eyebrow">NEWS & ANNOUNCEMENTS</p><h1 class="page-title">ข่าวสารและประกาศ</h1><p>ติดตามข้อมูลและเอกสารจากโรงพยาบาล</p></div></div>
@include('news.categories', ['categories' => $categories])
<form method="GET" action="{{ route('news.index') }}" class="search-form">
    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
    <label for="q">ค้นหาหัวข้อข่าว</label><div class="row"><input id="q" name="q" value="{{ request('q') }}" maxlength="150" placeholder="พิมพ์คำที่ต้องการค้นหา"><button class="button" type="submit">ค้นหา</button></div>
</form>
@include('news.list', ['posts' => $posts])
@include('news.pagination', ['paginator' => $posts])
@endsection