<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠一覧</title>
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
        <h1>勤怠一覧</h1>

        <div>
            <a href="{{ route('attendance.list', ['month' => $targetMonth->copy()->subMonth()->format('Y-m')]) }}">
                ← 前月
            </a>

            <strong>{{ $targetMonth->format('Y/m') }}</strong>

            <a href="{{ route('attendance.list', ['month' => $targetMonth->copy()->addMonth()->format('Y-m')]) }}">
                翌月 →
            </a>
        </div>

        <table border="1">
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
                @foreach ($days as $day)
                    @php
                        $dateKey = $day->format('Y-m-d');
                        $attendance = $attendances->get($dateKey);

                        $breakMinutes = 0;
                        $workMinutes = 0;

                        if ($attendance) {
                            foreach ($attendance->breakTimes as $breakTime) {
                                if ($breakTime->break_start && $breakTime->break_end) {
                                    $breakMinutes += \Carbon\Carbon::parse($breakTime->break_start)
                                        ->diffInMinutes(\Carbon\Carbon::parse($breakTime->break_end));
                                }
                            }

                            if ($attendance->clock_in && $attendance->clock_out) {
                                $workMinutes = \Carbon\Carbon::parse($attendance->clock_in)
                                    ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out)) - $breakMinutes;
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $day->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ $breakMinutes > 0 ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60) : '' }}</td>
                        <td>{{ $workMinutes > 0 ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60) : '' }}</td>
                        <td>
                            @if ($attendance)
                                <a href="#">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</body>
</html>