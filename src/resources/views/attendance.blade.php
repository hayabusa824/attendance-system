@extends('layout.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<main>
    <div class="container">
        <div class="status">{{ $statusLabel }}</div>
        <div class="date" id="currentDate"></div>
        <div class="time" id="currentTime"></div>

        @if ($message)
        <div class="farewell">{{ $message }}</div>
        @endif

        <div class="button-wrapper {{ $statusLabel === '勤務中' ? 'two-buttons' : 'one-button' }}">
            @if ($statusLabel === '勤務外')
            <form method="POST" action="/attendance/clock-in">@csrf
                <button class="btn btn-black">出勤</button>
            </form>
            @elseif ($statusLabel === '勤務中')
            <form method="POST" action="/attendance/clock-out">@csrf
                <button class="btn btn-black">退勤</button>
            </form>
            <form method="POST" action="/attendance/break-in">@csrf
                <button class="btn btn-white">休憩入</button>
            </form>
            @elseif ($statusLabel === '休憩中')
            <form method="POST" action="/attendance/break-out">@csrf
                <button class="btn btn-white">休憩戻</button>
            </form>
            @elseif ($statusLabel === '退勤済')
            @endif
        </div>
    </div>
</main>

    <script>
        function updateClock() {
        const now = new Date();
        const days = ['日', '月', '火', '水', '木', '金', '土'];
        document.getElementById('currentDate').textContent =
            `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日(${days[now.getDay()]})`;
        document.getElementById('currentTime').textContent =
            now.toLocaleTimeString('ja-JP', { hour: '2-digit', minute: '2-digit', hour12: false });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

@endsection