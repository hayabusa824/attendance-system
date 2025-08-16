@extends('layout.app-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceDetail.css') }}">
@endsection

@section('content')

<div class="container">
    <div class="title">勤怠詳細</div>

    <table class="attendance-detail">
        <tr class="tr">
            <td class="label">名前</td>
            <td class="date">{{ $request->user->name }}</td>
        </tr>

        <tr class="tr">
            <td class="label">日付</td>
            <td class="date">
                {{ \Carbon\Carbon::parse($request->target_date)->format('Y年n月j日') }}
            </td>
        </tr>

        <tr class="tr">
            <td class="label">出勤・退勤</td>
            <td class="input-inline">
                {{ $request->clock_in ?? '未入力' }}
                <span class="inline-separator">～</span>
                {{ $request->clock_out ?? '未入力' }}
            </td>
        </tr>

        @if (!empty($request->break_in) || !empty($request->break_out))
            <tr class="tr">
                <td class="label">休憩</td>
                <td class="input-inline">
                    {{ $request->break_in ?? '未入力' }}
                    <span class="inline-separator">～</span>
                    {{ $request->break_out ?? '未入力' }}
                </td>
            </tr>
        @endif

        <tr class="tr-2">
            <td class="label">備考</td>
            <td>{{ $request->reason }}</td>
        </tr>
    </table>

    <div class="btn-submit">
        @if ($request->status === 'approved')
            <button type="button" class="btn-btn-secondary" disabled>承認済み</button>
        @else
            <form method="POST" action="{{ route('stamp_correction_request.approve.action', $request->id) }}">
                @csrf
                <button type="submit" class="btn-btn-primary">承認</button>
            </form>
        @endif
    </div>
</div>

@endsection