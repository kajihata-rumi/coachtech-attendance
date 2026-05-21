@extends('layouts.user')

@section('title', '勤怠登録')

@section('content')

    <main class="attendance-page">
        <div class="attendance-page__inner">
            <div class="attendance-status">
                {{ $statusLabel }}
            </div>

            <div class="attendance-date">
                {{ $date }}
            </div>

            <div class="attendance-time">
                {{ $time }}
            </div>

            <div class="attendance-actions">
                @if ($status === 'before_work')
                    <form action="{{ route('attendance.clock_in') }}" method="POST">
                        @csrf
                        <button class="attendance-button attendance-button--black" type="submit">出勤</button>
                    </form>
                @elseif ($status === 'working')
                    <form action="{{ route('attendance.clock_out') }}" method="POST">
                        @csrf
                        <button class="attendance-button attendance-button--black" type="submit">退勤</button>
                    </form>

                    <form action="{{ route('attendance.break_start') }}" method="POST">
                        @csrf
                        <button class="attendance-button attendance-button--white" type="submit">休憩入</button>
                    </form>
                @elseif ($status === 'on_break')
                    <form action="{{ route('attendance.break_end') }}" method="POST">
                        @csrf
                        <button class="attendance-button attendance-button--white" type="submit">休憩戻</button>
                    </form>
                @elseif ($status === 'completed')
                    <p class="attendance-message">お疲れ様でした。</p>
                @endif
            </div>
        </div>
    </main>
@endsection