<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Request as RequestModel;
use App\Http\Requests\AttendanceCorrectionRequest;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $clockInRecord = Attendance::findOrFail($id);

        $userId = $clockInRecord->user_id;
        $date = $clockInRecord->date;

        $records = Attendance::where('user_id', $userId)
            ->whereDate('date', $date)
            ->orderBy('time')
            ->get();

        $clockIn = $records->firstWhere('type', 'clock_in');
        $clockOut = $records->firstWhere('type', 'clock_out');

        $breaks = [];
        $in = null;
        foreach ($records as $r) {
            if ($r->type === 'break_in') {
                $in = $r->time;
            } elseif ($r->type === 'break_out' && $in) {
                $breaks[] = ['start' => $in, 'end' => $r->time];
                $in = null;
            }
        }

        // 修正申請（承認待ち or 承認済み）を取得
        $request = \App\Models\Request::where('user_id', $userId)
            ->where('target_date', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->latest('applied_date')
            ->first();

        // 修正申請がある場合、出退勤と備考だけ上書き
        if ($request) {
            if (!empty($request->clock_in)) {
                $clockIn = (object)['time' => $request->clock_in];
            }
            if (!empty($request->clock_out)) {
                $clockOut = (object)['time' => $request->clock_out];
            }
            $note = $request->reason;
            $pendingRequest = $request;
        } else {
            $note = '';
            $pendingRequest = null;
        }

        return view('attendanceDetail', [
            'date' => \Carbon\Carbon::parse($date),
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaks' => $breaks,
            'note' => $note,
            'userName' => auth()->user()->name,
            'pendingRequest' => $pendingRequest,
        ]);
    }

    public function store(AttendanceCorrectionRequest $request)
    {
        $user = auth()->user();

        RequestModel::create([
            'user_id' => $user->id,
            'target_date' => $request->input('target_date'),
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'reason' => $request->input('reason'),
            'applied_date' => now(),
            'status' => 'pending',
            'break_in' => $request->input('break_in'),
            'break_out' => $request->input('break_out'),
        ]);

        return redirect()->route('attendance.detail', ['id' => $request->input('record_id')]);
    }

    public function showFromRequest($request_id)
    {
        $request = \App\Models\Request::with('user')->findOrFail($request_id);

        $userId = $request->user_id;
        $date = $request->target_date;

        $records = Attendance::where('user_id', $userId)
            ->whereDate('date', $date)
            ->orderBy('time')
            ->get();

        $clockIn = $records->firstWhere('type', 'clock_in');
        $clockOut = $records->firstWhere('type', 'clock_out');

        $breaks = [];
        $in = null;
        foreach ($records as $r) {
            if ($r->type === 'break_in') {
                $in = $r->time;
            } elseif ($r->type === 'break_out' && $in) {
                $breaks[] = ['start' => $in, 'end' => $r->time];
                $in = null;
            }
        }

        // 申請済みの出退勤を優先的に表示
        if (!empty($request->clock_in)) {
            $clockIn = (object)['time' => $request->clock_in];
        }
        if (!empty($request->clock_out)) {
            $clockOut = (object)['time' => $request->clock_out];
        }

        return view('attendanceDetail', [
            'date' => \Carbon\Carbon::parse($date),
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaks' => $breaks,
            'note' => $request->reason,
            'userName' => $request->user->name,
            'pendingRequest' => $request,
        ]);
    }
}
