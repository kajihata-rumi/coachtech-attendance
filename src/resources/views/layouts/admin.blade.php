<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/admin/attendance/list">
            <img class="header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
        </a>

        <nav>
            <a href="/admin/attendance/list">勤怠一覧</a>
            <a href="/admin/staff/list">スタッフ一覧</a>
            <a href="/stamp_correction_request/list">申請一覧</a>

            <form action="{{ route('admin.logout') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    @yield('content')
</body>

</html>