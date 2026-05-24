@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('content')
    <main class="admin-staff-list">
        <h1 class="admin-staff-list__title">スタッフ一覧</h1>

        <table class="admin-staff-list__table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($staffs as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>
                            <a href="/admin/attendance/staff/{{ $staff->id }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
@endsection
