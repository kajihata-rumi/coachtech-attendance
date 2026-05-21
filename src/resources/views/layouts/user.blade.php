<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <header class="header">
        <a class="header__logo" href="/attendance">
            <img class="header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
        </a>

        <nav>
            @if (($status ?? null) === 'completed')
                <a href="/attendance/list">今月の出勤一覧</a>
                <a href="/stamp_correction_request/list">申請一覧</a>
            @else
                <a href="/attendance">勤怠</a>
                <a href="/attendance/list">勤怠一覧</a>
                <a href="/stamp_correction_request/list">申請</a>
            @endif

            <form action="/logout" method="POST" style="display:inline">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    @yield('content')
</body>

</html>

