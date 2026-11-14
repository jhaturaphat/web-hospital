@extends('layouts.hospital')
@section('title', 'จัดการข่าวสาร')
@section('content')
<div class="section-heading"><div><p class="eyebrow">CONTENT MANAGEMENT</p><h1 class="page-title">จัดการข่าวสาร</h1><p>สวัสดี {{ auth()->user()->name }} · เพิ่มข่าวและไฟล์แนบได้ด้วยตัวเอง</p></div><a class="button" href="{{ route('staff.posts.create') }}">+ เพิ่มข่าวใหม่</a></div>
<div class="management-grid"><section><h2>ข่าวทั้งหมด</h2><div class="staff-posts">
@forelse($posts as $post)
<article class="staff-post"><div><span class="badge">{{ $post->is_published ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}</span><h3>{{ $post->title }}</h3><p>{{ $post->category->name }}</p></div><div class="actions"><a class="text-link" href="{{ route('staff.posts.edit', $post) }}">แก้ไข →</a><a class="text-link" href="{{ route('news.show', $post) }}">ดูข่าว ↗</a></div></article>
@empty<div class="empty-state">ยังไม่มีข่าว เริ่มต้นด้วยปุ่ม “เพิ่มข่าวใหม่”</div>@endforelse
</div>@include('news.pagination', ['paginator' => $posts])</section>
<aside class="category-panel"><h2>หมวดหมู่ข่าว</h2><p class="form-help">เพิ่มหัวข้อได้ตามหน่วยงานหรือประเภทข่าว</p><form method="POST" action="{{ route('staff.categories.store') }}" class="editor-form">@csrf<label for="new-category">ชื่อหมวดหมู่ใหม่</label><input id="new-category" name="name" required maxlength="100" value="{{ old('name') }}"><button class="button" type="submit">+ เพิ่มหมวดหมู่</button></form>
@foreach($categories as $category)
<details class="category-editor"><summary>{{ $category->name }} <small>({{ $category->posts_count }})</small></summary><form method="POST" action="{{ route('staff.categories.update', $category) }}" class="editor-form">@csrf @method('PUT')<label for="category-{{ $category->id }}">ชื่อหมวดหมู่</label><input id="category-{{ $category->id }}" name="name" value="{{ $category->name }}" maxlength="100" required><button class="button button-outline" type="submit">บันทึกชื่อ</button></form>@if($category->posts_count === 0)<form method="POST" action="{{ route('staff.categories.destroy', $category) }}">@csrf @method('DELETE')<button class="danger-link" type="submit">ลบหมวดหมู่ว่างนี้</button></form>@endif</details>
@endforeach</aside></div>
@endsection