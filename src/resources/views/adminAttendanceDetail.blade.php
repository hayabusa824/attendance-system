@extends('layout.app-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceDetail.css') }}">
@endsection

@section('content')

<div class="container">
    <div class="title">勤怠詳細</div>

    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.attendance.update', ['id' => $attendanceId]) }}" method="POST">
        @csrf
        <table class="attendance-detail">
            <tr class="tr">
                <td class="label">名前</td>
                <td class='date'>{{ $userName }}</td>
            </tr>
            <tr class="tr">
                <td class="label">日付</td>
                <td class='date'>
                    <input type="hidden" name="target_date" value="{{ $date->format('Y-m-d') }}">
                    {{ $date->format('Y年') }}
                    <span class="date-separator"></span>
                    {{ $date->format('n月j日') }}
                </td>
            </tr>
            <tr class="tr">
                <td class="label">出勤・退勤</td>
                <td class="input-inline">
                    <input type="text" name="clock_in" value="{{ optional($clockIn)->time ? \Carbon\Carbon::parse($clockIn->time)->format('H:i') : '' }}">
                    <span class="inline-separator">～</span>
                    <input type="text" name="clock_out" value="{{ optional($clockOut)->time ? \Carbon\Carbon::parse($clockOut->time)->format('H:i') : '' }}">
                </td>
            </tr>

            <tr class="tr">
                <td class="label">休憩</td>
                <td class="input-inline">
                    <input type="text" name="break_in" value="{{ isset($breaks[0]['start']) ? \Carbon\Carbon::parse($breaks[0]['start'])->format('H:i') : '' }}">
                    <span class="inline-separator">～</span>
                    <input type="text" name="break_out" value="{{ isset($breaks[0]['end']) ? \Carbon\Carbon::parse($breaks[0]['end'])->format('H:i') : '' }}">
                </td>
            </tr>

            <tr class="tr-2">
                <td class="label">備考</td>
                <td>
                    <textarea name="reason">{{ old('reason') }}</textarea>
                </td>
            </tr>
        </table>

        <div class="btn-submit">
            @if(isset($status) && ($status === 'approved' || $status === 'updated'))
                <button type="button" class="btn-btn-secondary" disabled>修正済み</button>
            @else
                <button type="submit" class="btn-btn-primary">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection