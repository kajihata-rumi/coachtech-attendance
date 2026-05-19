<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>申請一覧</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>
    <header class="header">
    <a class="header__logo" href="{{ $isAdmin ? route('admin.attendance.list') : route('attendance.index') }}">
        <img class="header__logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
    </a>

    <nav>
        @if ($isAdmin)
            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
            <a href="{{ route('stamp_correction_request.list') }}">申請一覧</a>
        @else
            <a href="{{ route('attendance.index') }}">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        @endif

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

        <div class="correction-request__tabs">
            <a
                class="correction-request__tab {{ request('tab', 'pending') === 'pending' ? 'is-active' : '' }}"
                href="{{ url('/stamp_correction_request/list?tab=pending') }}"
            >
                承認待ち
            </a>

            <a
                class="correction-request__tab {{ request('tab') === 'approved' ? 'is-active' : '' }}"
                href="{{ url('/stamp_correction_request/list?tab=approved') }}"
            >
                承認済み
            </a>
        </div>

        <table class="correction-request__table">
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
                        @if (auth()->user()->role === 'admin')
                            <a class="table-detail-link" href="/stamp_correction_request/approve/{{ $request->id }}">
                                詳細
                            </a>
                        @else
                            <a class="table-detail-link" href="{{ route('attendance.show', ['attendance' => $request->attendance_id]) }}">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </main>
</body>

</html>