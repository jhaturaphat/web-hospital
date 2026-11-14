@extends('layouts.hospital')
@section('title', $post->exists ? 'แก้ไขข่าว' : 'เพิ่มข่าวใหม่')
@section('content')
<a class="text-link" href="{{ route('staff.posts.index') }}">← กลับหน้าจัดการข่าว</a>
<div class="editor-panel"><h1 class="page-title">{{ $post->exists ? 'แก้ไขข่าว' : 'เพิ่มข่าวใหม่' }}</h1><p>กรอกเนื้อหา เลือกหมวดหมู่ และแนบเอกสารที่ต้องการเผยแพร่</p>
<form method="POST" enctype="multipart/form-data" action="{{ $post->exists ? route('staff.posts.update', $post) : route('staff.posts.store') }}" class="editor-form">
    @csrf
    @if($post->exists) @method('PUT') @endif
    <label for="title">หัวข้อข่าว <span aria-hidden="true">*</span></label><input id="title" name="title" required maxlength="255" value="{{ old('title', $post->title) }}">
    <label for="news_category_id">หมวดหมู่ *</label><select id="news_category_id" name="news_category_id" required><option value="">เลือกหมวดหมู่</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)old('news_category_id', $post->news_category_id) === (string)$category->id)>{{ $category->name }}</option>@endforeach</select>
    <label for="body">รายละเอียดข่าว *</label><textarea id="body" name="body" rows="12" maxlength="50000" required>{{ old('body', $post->body) }}</textarea>
    <label for="attachments">เพิ่มไฟล์แนบ</label><input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" aria-describedby="upload-help"><p id="upload-help" class="form-help">PDF, Word, Excel, JPG หรือ PNG · ไฟล์ละไม่เกิน 2 MB · ครั้งละไม่เกิน 3 ไฟล์<br>การแก้ไขจะเก็บไฟล์เดิมไว้ สามารถเพิ่มหรือลบไฟล์แต่ละรายการด้านล่าง</p>
    <label for="is_published">สถานะข่าว</label><select id="is_published" name="is_published"><option value="0" @selected(!old('is_published', $post->is_published))>ฉบับร่าง (เจ้าหน้าที่เท่านั้น)</option><option value="1" @selected(old('is_published', $post->is_published))>เผยแพร่บนเว็บไซต์</option></select>
    <div class="actions"><button class="button" type="submit">บันทึกข่าว</button><a class="text-link" href="{{ route('staff.posts.index') }}">กลับหน้าจัดการ</a></div>
</form>
@if($post->exists)
<section class="attachments"><h2>ไฟล์แนบปัจจุบัน</h2><ul>@forelse($post->attachments as $attachment)<li><a href="{{ route('news.download', $attachment) }}">↓ {{ $attachment->name }}</a><form method="POST" action="{{ route('staff.attachments.destroy', [$post, $attachment]) }}">@csrf @method('DELETE')<button class="danger-link" type="submit">ลบไฟล์</button></form></li>@empty<li>ยังไม่มีไฟล์แนบ</li>@endforelse</ul></section>
<details class="delete-panel"><summary>ลบข่าวนี้</summary><p>ข่าวและไฟล์แนบทั้งหมดจะถูกลบถาวร</p><form method="POST" action="{{ route('staff.posts.destroy', $post) }}">@csrf @method('DELETE')<button class="button button-danger" type="submit">ยืนยันลบข่าวและไฟล์แนบทั้งหมด</button></form></details>
@endif</div>
@endsection