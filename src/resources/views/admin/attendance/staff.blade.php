<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>スタッフ別勤怠一覧</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

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

<main class="admin-staff-attendance">
    <div class="admin-staff-attendance__inner">
        <h1 class="admin-staff-attendance__title">
            {{ $staff->name }}さんの勤怠
        </h1>

        <div class="admin-staff-attendance__month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">
                ← 前月
            </a>

            <div class="admin-staff-attendance__month">
                <img
                    class="admin-staff-attendance__calendar-icon"
                    src="{{ asset('images/calendar-icon.png') }}"
                    alt="カレンダーアイコン"
                >
                <strong>{{ $currentMonth->format('Y/m') }}</strong>
            </div>

            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">
                翌月 →
            </a>
        </div>

<table class="admin-staff-attendance__table">
    <thead>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>
        <tbody>
            @foreach ($dates as $date)
                @php
                    $dateKey = $date->format('Y-m-d');
                    $attendance = $attendances->get($dateKey);
                    $week = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];

                    $breakMinutes = 0;
                    $totalMinutes = null;

                    if ($attendance) {
                        foreach ($attendance->breakTimes as $breakTime) {
                            if ($breakTime->break_start && $breakTime->break_end) {
                                $breakMinutes += \Carbon\Carbon::parse($breakTime->break_start)
                                    ->diffInMinutes(\Carbon\Carbon::parse($breakTime->break_end));
                            }
                        }

                        if ($attendance->clock_in && $attendance->clock_out) {
                            $workMinutes = \Carbon\Carbon::parse($attendance->clock_in)
                                ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out));

                            $totalMinutes = $workMinutes - $breakMinutes;
                        }
                    }

                    $breakTimeText = $breakMinutes > 0
                        ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '';

                    $totalTimeText = $totalMinutes !== null
                        ? floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT)
                        : '';
                @endphp

                <tr>
                    <td>{{ $date->format('m/d') }}({{ $week }})</td>

                    <td>
                        {{ $attendance && $attendance->clock_in
                            ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                            : '' }}
                    </td>

                    <td>
                        {{ $attendance && $attendance->clock_out
                            ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                            : '' }}
                    </td>

                    <td>{{ $breakTimeText }}</td>

                    <td>{{ $totalTimeText }}</td>

                    <td>
                        @if ($attendance)
                            <a href="{{ route('admin.attendance.show', ['attendance' => $attendance->id]) }}">
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
    <div class="admin-staff-attendance__csv">
    <a
        class="admin-staff-attendance__csv-button"
        href="{{ route('admin.attendance.staff.csv', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]) }}"
    >
        CSV出力
    </a>
    </div>
</main>
</html>