<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者：勤怠一覧</title>
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
            <a href="/admin/stamp_correction_request/list">申請一覧</a>

            <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main>
        <h1>{{ $targetDate->format('Y年n月j日') }}の勤怠</h1>

        <div class="attendance-list__month-nav">
            <a class="attendance-list__month-link" href="{{ route('admin.attendance.list', ['date' => $previousDate->toDateString()]) }}">
                ← 前日
            </a>

            <div class="attendance-list__current-month">
                <img
                    class="attendance-list__calendar-icon"
                    src="{{ asset('images/calendar-icon.png') }}"
                    alt="カレンダーアイコン"
                >
                <strong>{{ $targetDate->format('Y/m/d') }}</strong>
            </div>

            <a class="attendance-list__month-link" href="{{ route('admin.attendance.list', ['date' => $nextDate->toDateString()]) }}">
                翌日 →
            </a>
        </div>

        <table class="correction-request__table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($attendanceRows as $row)
                    <tr>
                        <td>{{ $row['user']->name }}</td>
                        <td>{{ $row['clock_in'] }}</td>
                        <td>{{ $row['clock_out'] }}</td>
                        <td>{{ $row['break_time'] }}</td>
                        <td>{{ $row['total_time'] }}</td>
                        <td>
                            @if ($row['attendance'])
                                <a class="table-detail-link" href="{{ url('/admin/attendance/' . $row['attendance']->id) }}">
                                    詳細
                                </a>
                            @else
                                詳細
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</body>
</html>