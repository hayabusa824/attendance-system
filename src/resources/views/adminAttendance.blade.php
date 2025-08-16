@extends('layout.app-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')


<div class='container'>
    <div class="title">
        <div class="title-txt">{{ $date->format('Y年n月j日') }}の勤務</div>
    </div>

    {{-- 日付セレクター --}}
    <div class="month-selector">
        <a href="?date={{ $prevDate->toDateString() }}" class="btn btn-prev">&larr; 前日</a>
        <div class="title-month">
            <img src="{{ asset('img/calendar.png') }}" class="title-icon">
            <div class="month-label">{{ $date->format('Y/m/d') }}</div>
        </div>
        <a href="?date={{ $nextDate->toDateString() }}" class="btn btn-next">翌日 &rarr;</a>
    </div>

    <table class="attendance-table">
        <tr class="table">
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($records as $record)
            <tr class="table-row">
                <td>{{ $record['name'] }}</td>
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
</div>

@endsection