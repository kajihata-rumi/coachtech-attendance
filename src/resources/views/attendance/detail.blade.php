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

        @php
            $break1 = $attendance->breakTimes->get(0);
            $break2 = $attendance->breakTimes->get(1);
        @endphp

@if (!$isPendingCorrection)
    <form action="{{ route('attendance.correction.store', $attendance) }}" method="POST">
        @csrf
@endif

<table border="1">
    @if ($isPendingCorrection)
        {{-- 承認待ち：表示専用 --}}
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
                    $pendingCorrectionRequest->requested_clock_in
                        ? \Carbon\Carbon::parse($pendingCorrectionRequest->requested_clock_in)->format('H:i')
                        : ''
                }}
            </td>
            <td>〜</td>
            <td>
                {{
                    $pendingCorrectionRequest->requested_clock_out
                        ? \Carbon\Carbon::parse($pendingCorrectionRequest->requested_clock_out)->format('H:i')
                        : ''
                }}
            </td>
        </tr>

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

        <tr>
            <th>備考</th>
            <td colspan="3">{{ $pendingCorrectionRequest->reason }}</td>
        </tr>
    @else
        {{-- 通常時：編集可能 --}}
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
                <input
                    type="text"
                    name="clock_in"
                    value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                >
            </td>
            <td>〜</td>
            <td>
                <input
                    type="text"
                    name="clock_out"
                    value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                >
            </td>
        </tr>

        <tr>
            <th>休憩</th>
            <td>
                <input type="hidden" name="breaks[0][id]" value="{{ $break1 ? $break1->id : '' }}">
                <input
                    type="text"
                    name="breaks[0][start]"
                    value="{{ old('breaks.0.start', $break1 && $break1->break_start ? \Carbon\Carbon::parse($break1->break_start)->format('H:i') : '') }}"
                >
            </td>
            <td>〜</td>
            <td>
                <input
                    type="text"
                    name="breaks[0][end]"
                    value="{{ old('breaks.0.end', $break1 && $break1->break_end ? \Carbon\Carbon::parse($break1->break_end)->format('H:i') : '') }}"
                >
            </td>
        </tr>

        <tr>
            <th>休憩2</th>
            <td>
                <input type="hidden" name="breaks[1][id]" value="{{ $break2 ? $break2->id : '' }}">
                <input
                    type="text"
                    name="breaks[1][start]"
                    value="{{ old('breaks.1.start', $break2 && $break2->break_start ? \Carbon\Carbon::parse($break2->break_start)->format('H:i') : '') }}"
                >
            </td>
            <td>〜</td>
            <td>
                <input
                    type="text"
                    name="breaks[1][end]"
                    value="{{ old('breaks.1.end', $break2 && $break2->break_end ? \Carbon\Carbon::parse($break2->break_end)->format('H:i') : '') }}"
                >
            </td>
        </tr>

        <tr>
            <th>備考</th>
            <td colspan="3">
                <textarea name="reason">{{ old('reason', $attendance->remarks ?? '') }}</textarea>
            </td>
        </tr>
    @endif
</table>

@if (!$isPendingCorrection && $errors->any())
    <div style="color: red;">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

        @if ($isPendingCorrection)
            <p style="color: red;">*承認待ちのため修正はできません。</p>
        @else
            <button type="submit">修正</button>
            </form>
        @endif
    </main>
</body>
</html>