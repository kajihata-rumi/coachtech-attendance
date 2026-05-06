<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠登録</title>
</head>
<body>
    <header>
        <div>COACHTECH</div>

        <nav>
            <a href="/attendance">勤怠</a>
            <a href="/attendance/list">勤怠一覧</a>
            <a href="/stamp_correction_request/list">申請</a>

            <form action="/logout" method="POST" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main>
        <div>{{ $statusLabel }}</div>

        <h2>{{ $date }}</h2>
        <h1>{{ $time }}</h1>

        @if ($status === 'before_work')
            <form action="{{ route('attendance.clock_in') }}" method="POST">
                @csrf
                <button type="submit">出勤</button>
            </form>
        @elseif ($status === 'working')
            <button type="button">退勤</button>
            <button type="button">休憩入</button>
        @elseif ($status === 'on_break')
            <button type="button">休憩戻</button>
        @elseif ($status === 'finished')
            <p>お疲れ様でした。</p>
        @endif
    </main>
</body>
</html>