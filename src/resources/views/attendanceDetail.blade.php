@extends('layout.app')

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

    @if (empty($pendingRequest))
    <form method="POST" action="{{ route('correction.store') }}">
        @csrf
        <input type="hidden" name="target_date" value="{{ $date->toDateString() }}">
        <input type="hidden" name="record_id" value="{{ $clockIn->id ?? '' }}">
    @endif

    <table class="attendance-detail">
        <tr class="tr">
            <td class="label">名前</td>
            <td class='date'>{{ $userName }}</td>
        </tr>
        <tr class="tr">
            <td class="label">日付</td>
            <td class='date'>
                {{ $date->format('Y年') }}
                <span class="date-separator"></span>
                {{ $date->format('n月j日') }}
            </td>
        </tr>

        @if (!empty($pendingRequest))
            <tr class="tr">
                <td class="label">出勤・退勤</td>
                <td class="input-inline">
                    {{ $pendingRequest->clock_in ?? '未入力' }}
                    <span class="inline-separator">～</span>
                    {{ $pendingRequest->clock_out ?? '未入力' }}
                </td>
            </tr>
            @if (!empty($pendingRequest->break_in) || !empty($pendingRequest->break_out))
                <tr class="tr">
                    <td class="label">休憩</td>
                    <td class="input-inline">
                        {{ $pendingRequest->break_in ?? '未入力' }}
                        <span class="inline-separator">～</span>
                        {{ $pendingRequest->break_out ?? '未入力' }}
                    </td>
                </tr>
            @endif
            <tr class="tr-2">
                <td class="label">備考</td>
                <td>{{ $pendingRequest->reason }}</td>
            </tr>
        @else
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
                <td><textarea name="reason">{{ old('reason') }}</textarea></td>
            </tr>
        @endif
    </table>

    @if (empty($pendingRequest))
        <div class="btn-submit">
            <button type="submit" class='btn-btn-primary'>修正</button>
        </div>
    </form>
    @else
        <p class="text-danger">※承認待ちのため修正はできません。</p>
    @endif
</div>

@endsection