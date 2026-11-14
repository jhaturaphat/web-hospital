<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#a73565">
    <title>@yield('title', 'ข่าวสาร') | โรงพยาบาลสมเด็จพระยุพราชเดชอุดม</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/hospital.css') }}">
</head>
<body>
<a class="skip-link" href="#main">ข้ามไปเนื้อหาหลัก</a>
<div class="topbar"><div class="container row"><span>ด้วยความห่วงใย เพื่อสุขภาพของทุกคน</span><a href="tel:045362028">ติดต่อโรงพยาบาล 045-362028 ↗</a></div></div>
<header><div class="container header-inner"><a class="brand" href="{{ route('home') }}"><span class="brand-mark" aria-hidden="true">✚</span><span>โรงพยาบาลสมเด็จพระยุพราชเดชอุดม<small>DET UDOM CROWN PRINCE HOSPITAL</small></span></a><nav aria-label="เมนูหลัก"><a href="{{ route('home') }}">หน้าแรก</a><a href="{{ route('news.index') }}">ข่าวสาร</a><a href="{{ route('home') }}#contact">ติดต่อเรา</a>@can('manage-news')<a href="{{ route('staff.posts.index') }}">จัดการข่าว</a>@endcan</nav></div></header>
<main id="main" class="container section">
    @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
    @if($errors->any())
        <div class="error-box" role="alert"><strong>กรุณาตรวจสอบข้อมูล</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
<footer><div class="container row"><span>โรงพยาบาลสมเด็จพระยุพราชเดชอุดม</span>@can('manage-news')<form method="POST" action="{{ route('staff.logout') }}">@csrf<button class="button" type="submit">ออกจากระบบ</button></form>@else<a href="{{ route('login') }}">สำหรับเจ้าหน้าที่ →</a>@endcan</div></footer>
</body>
</html>