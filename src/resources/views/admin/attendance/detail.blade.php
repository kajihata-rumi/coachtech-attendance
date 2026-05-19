<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>管理者：勤怠詳細</title>
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
            <a href="{{ route('stamp_correction_request.list') }}">申請一覧</a>

            <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main>
        <h1>勤怠詳細</h1>

        @php
            $break1 = $attendance->breakTimes->get(0);
            $break2 = $attendance->breakTimes->get(1);
        @endphp

        <form action="{{ route('admin.attendance.update', $attendance) }}" method="POST">
    @csrf
    @method('PATCH')
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>
                        <div class="detail-table__name">
                            <span>{{ $user->name }}</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        <div class="detail-table__date">
                            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="detail-table__time-range">
                            <input
                                class="detail-table__time-input"
                                type="text"
                                name="clock_in"
                                value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}"
                            >
                            <span>〜</span>
                            <input
                                class="detail-table__time-input"
                                type="text"
                                name="clock_out"
                                value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}"
                            >
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="detail-table__time-range">
                            <input
                                class="detail-table__time-input"
                                type="text"
                                name="breaks[0][start]"
                                value="{{ $break1 && $break1->break_start ? \Carbon\Carbon::parse($break1->break_start)->format('H:i') : '' }}"
                            >
                            <span>〜</span>
                            <input
                                class="detail-table__time-input"
                                type="text"
                                name="breaks[0][end]"
                                value="{{ $break1 && $break1->break_end ? \Carbon\Carbon::parse($break1->break_end)->format('H:i') : '' }}"
                            >
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>休憩2</th>
                    <td>
                        <div class="detail-table__time-range">
                            <input
                                class="detail-table__time-input"
                                type="text"
                                name="breaks[1][start]"
                                value="{{ $break2 && $break2->break_start ? \Carbon\Carbon::parse($break2->break_start)->format('H:i') : '' }}"
                            >
                            <span>〜</span>
                            <input
                                class="detail-table__time-input"
                                type="text"
                                name="breaks[1][end]"
                                value="{{ $break2 && $break2->break_end ? \Carbon\Carbon::parse($break2->break_end)->format('H:i') : '' }}"
                            >
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <div class="detail-table__reason">
                            <textarea
                                class="detail-table__textarea"
                                name="note"
                            >{{ $attendance->note ?? '' }}</textarea>
                        </div>
                    </td>
                </tr>
            </table>

            @if ($errors->any())
                <div class="detail__errors">
                    @foreach ($errors->all() as $error)
                        <p class="error-message">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="detail__footer">
                <button class="detail__submit-button" type="submit">修正</button>
            </div>
        </form>
    </main>
</body>
</html>