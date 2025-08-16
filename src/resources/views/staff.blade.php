@extends('layout.app-admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
<link rel="stylesheet" href="{{ asset('css/attendanceList.css') }}">
@endsection

@section('content')

<div class="container">
    <div class='title'>
        <div class="title-txt">スタッフ一覧</div>
    </div>

    <table class="attendance-table">
        <tr class="table">
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
        </tr>

        @foreach ($users as $user)
            <tr class="table-row">
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>

@endsection