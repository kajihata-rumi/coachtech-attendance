<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠登録</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="header">
        <a class="header__logo" href="/attendance">
    <img class="header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
</a>

        <nav>
            @if ($status === 'completed')
                <a href="/attendance/list">今月の出勤一覧</a>
                <a href="/stamp_correction_request/list">申請一覧</a>
            @else
                <a href="/attendance">勤怠</a>
                <a href="/attendance/list">勤怠一覧</a>
                <a href="/stamp_correction_request/list">申請</a>
            @endif

            <form action="/logout" method="POST" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

<main class="attendance-page">
    <div class="attendance-page__inner">
        <div class="attendance-status">
            {{ $statusLabel }}
        </div>

        <div class="attendance-date">
            {{ $date }}
        </div>

        <div class="attendance-time">
            {{ $time }}
        </div>

        <div class="attendance-actions">
            @if ($status === 'before_work')
                <form action="{{ route('attendance.clock_in') }}" method="POST">
                    @csrf
                    <button class="attendance-button attendance-button--black" type="submit">出勤</button>
                </form>
            @elseif ($status === 'working')
                <form action="{{ route('attendance.clock_out') }}" method="POST">
                    @csrf
                    <button class="attendance-button attendance-button--black" type="submit">退勤</button>
                </form>

                <form action="{{ route('attendance.break_start') }}" method="POST">
                    @csrf
                    <button class="attendance-button attendance-button--white" type="submit">休憩入</button>
                </form>
            @elseif ($status === 'on_break')
                <form action="{{ route('attendance.break_end') }}" method="POST">
                    @csrf
                    <button class="attendance-button attendance-button--white" type="submit">休憩戻</button>
                </form>
            @elseif ($status === 'completed')
                <p class="attendance-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</main>
</body>
</html>
