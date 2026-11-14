@extends('layouts.hospital')
@section('title', 'เข้าสู่ระบบเจ้าหน้าที่')
@section('content')
<div class="login-panel"><p class="eyebrow">STAFF ACCESS</p><h1 class="page-title">เข้าสู่ระบบเจ้าหน้าที่</h1><p>สำหรับจัดการข่าวสารและเอกสารของโรงพยาบาล</p>
<form method="POST" action="{{ route('login.store') }}" class="editor-form">
    @csrf
    <label for="email">อีเมล</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus>
    <label for="password">รหัสผ่าน</label><input id="password" type="password" name="password" required autocomplete="current-password">
    <button class="button" type="submit">เข้าสู่ระบบ →</button>
</form><p class="form-help">หากยังไม่มีบัญชี กรุณาติดต่อผู้ดูแลระบบของโรงพยาบาล</p></div>
@endsection