@extends('layout.app-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/requestList.css') }}">
@endsection

@section('content')

<div class="container">
    <div class="title">
        <div>申請一覧</div>
    </div>

    <div class="tabs">
        <a href="?status=pending" class="tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <table class="attendance-table">
            <tr class="table">
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
                <tbody>
            @foreach ($requests as $request)
                <tr class="table-row">
                    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->target_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->reason }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->applied_date)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('stamp_correction_request.approve', ['attendance_correct_request' => $request->id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection