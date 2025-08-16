@extends('layout.app-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')

<div class="container">
    <div class='title'>
        <div class="title-txt">{{ $user->name }}さんの勤務</div>
    </div>

    <div class="month-selector">
        <a href="?year={{ $year }}&month={{ $month - 1 }}" class="btn btn-prev">&larr; 前月</a>
        <div class="title-month">
            <img src="{{ asset('img/calendar.png') }}" class="title-icon">
            <div class="month-label">{{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</div>
        </div>
        <a href="?year={{ $year }}&month={{ $month + 1 }}" class="btn btn-next">翌月 &rarr;</a>
    </div>

    <table class="attendance-table">
        <tr class="table">
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($records as $record)
            <tr class="table-row">
                <td>{{ $record['date_label'] }}</td>
                <td>{{ $record['clock_in'] }}</td>
                <td>{{ $record['clock_out'] }}</td>
                <td>{{ $record['break'] }}</td>
                <td>{{ $record['total'] }}</td>
                <td>
                    @if (!empty($record['id']))
                        <a href="{{ route('admin.attendance.detail', ['id' => $record['id']]) }}" class="detail-link">詳細</a>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <form method="GET" action="{{ route('admin.attendance.csv') }}">
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <div class="btn-submit">
            <button type="submit">CSV出力</button>
        </div>
    </form>

</div>

@endsection