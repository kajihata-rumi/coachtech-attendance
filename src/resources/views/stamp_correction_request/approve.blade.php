<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠詳細</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="header">
    <a class="header__logo" href="{{ route('admin.attendance.list') }}">
        <img class="header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
    </a>

    <nav>
        <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請一覧</a>

        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </nav>
</header>

    <main class="attendance-detail">
        <h1 class="page-title">勤怠詳細</h1>

        <table class="attendance-detail-table approval-detail-table">
    <tr>
        <th>名前</th>
        <td>
            <div class="detail-table__name">
                <span>{{ $correctionRequest->user_name }}</span>
            </div>
        </td>
    </tr>

    <tr>
        <th>日付</th>
        <td>
            <div class="detail-table__date">
                <span>{{ \Carbon\Carbon::parse($correctionRequest->attendance_date)->format('Y年') }}</span>
                <span>{{ \Carbon\Carbon::parse($correctionRequest->attendance_date)->format('n月j日') }}</span>
            </div>
        </td>
    </tr>

    <tr>
        <th>出勤・退勤</th>
        <td>
            <div class="detail-table__time-range">
                <span>{{ \Carbon\Carbon::parse($correctionRequest->requested_clock_in)->format('H:i') }}</span>
                <span>〜</span>
                <span>{{ \Carbon\Carbon::parse($correctionRequest->requested_clock_out)->format('H:i') }}</span>
            </div>
        </td>
    </tr>

    <tr>
        <th>休憩</th>
        <td>
            <div class="detail-table__time-range">
                <span>12:00</span>
                <span>〜</span>
                <span>13:00</span>
            </div>
        </td>
    </tr>

    <tr>
        <th>休憩2</th>
        <td>
            <div class="detail-table__time-range">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </td>
    </tr>

    <tr>
        <th>備考</th>
        <td>
            <div class="detail-table__reason">
                <span>{{ $correctionRequest->reason }}</span>
            </div>
        </td>
    </tr>
</table>

        <div class="approval-button-area">
            @if ($correctionRequest->status === 'approved')
                <button class="approval-button approved" disabled>承認済み</button>
            @else
                <button class="approval-button">承認</button>
            @endif
        </div>
    </main>
</body>
</html>