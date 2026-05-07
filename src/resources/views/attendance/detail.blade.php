<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠詳細</title>
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
        @php
        $isPendingCorrection = !is_null($pendingCorrectionRequest);
        @endphp

        <h1>勤怠詳細</h1>

        <table border="1">
            <tr>
                <th>名前</th>
                <td colspan="3">{{ $user->name }}</td>
            </tr>

            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</td>
                <td colspan="2">{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{
                        $isPendingCorrection && $pendingCorrectionRequest->requested_clock_in
                            ? \Carbon\Carbon::parse($pendingCorrectionRequest->requested_clock_in)->format('H:i')
                            : ($attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '')
                    }}
                </td>
                <td>〜</td>
                <td>
                    {{
                        $isPendingCorrection && $pendingCorrectionRequest->requested_clock_out
                            ? \Carbon\Carbon::parse($pendingCorrectionRequest->requested_clock_out)->format('H:i')
                            : ($attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '')
                    }}
                </td>
            </tr>

            @if ($isPendingCorrection && $pendingCorrectionRequest->breakCorrectionRequests->count() > 0)
                @foreach ($pendingCorrectionRequest->breakCorrectionRequests as $index => $breakCorrectionRequest)
                    <tr>
                        <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                        <td>
                            {{
                                $breakCorrectionRequest->requested_break_start
                                    ? \Carbon\Carbon::parse($breakCorrectionRequest->requested_break_start)->format('H:i')
                                    : ''
                            }}
                        </td>
                        <td>〜</td>
                        <td>
                            {{
                                $breakCorrectionRequest->requested_break_end
                                    ? \Carbon\Carbon::parse($breakCorrectionRequest->requested_break_end)->format('H:i')
                                    : ''
                            }}
                        </td>
                    </tr>
                @endforeach
            @elseif ($attendance->breakTimes->count() > 0)
                @foreach ($attendance->breakTimes as $index => $breakTime)
                    <tr>
                        <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                        <td>
                            {{ $breakTime->break_start ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i') : '' }}
                        </td>
                        <td>〜</td>
                        <td>
                            {{ $breakTime->break_end ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i') : '' }}
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <th>休憩</th>
                    <td></td>
                    <td>〜</td>
                    <td></td>
                </tr>
            @endif

            <tr>
                <th>備考</th>
                <td colspan="3">
                    <textarea readonly>{{ $isPendingCorrection ? $pendingCorrectionRequest->reason : ($attendance->remarks ?? '') }}</textarea>
                </td>
            </tr>
        </table>

        @if ($isPendingCorrection)
            <p style="color: red;">*承認待ちのため修正はできません。</p>
        @else
            <button type="button">修正</button>
        @endif
    </main>
</body>
</html>