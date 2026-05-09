<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>申請一覧</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <header>
        <a class="header__logo" href="/attendance">
    <img class="header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
</a>
        <nav>
            <a href="{{ route('attendance.index') }}">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('stamp_correction_request.list') }}">申請</a>

            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main>
        <h1>申請一覧</h1>

        @php
            $tab = request('tab', 'pending');
            $requests = $tab === 'approved' ? $approvedRequests : $pendingRequests;
        @endphp

        <div>
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}">承認待ち</a>
            <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}">承認済み</a>
        </div>

        <table border="1">
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>

            @foreach ($requests as $request)
                <tr>
                    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->user_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.show', ['attendance' => $request->attendance_id]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </main>
</body>

</html>