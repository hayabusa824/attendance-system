<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Request as RequestModel;

class AdminAttendanceDetailController extends Controller
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

        $userName = $clockInRecord->user->name;

        $requestRecord = RequestModel::where('user_id', $userId)
            ->where('target_date', Carbon::parse($date)->toDateString())
            ->first();

        $status = $requestRecord ? $requestRecord->status : session('status');

        return view('adminAttendanceDetail', [
            'attendanceId' => $id,
            'date' => \Carbon\Carbon::parse($date),
            'clockIn' => $clockIn,
            'clockOut' => $clockOut,
            'breaks' => $breaks,
            'userName' => $userName,
            'status' => $status,
        ]);
    }

    public function update(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $userId = $attendance->user_id;
        $targetDate = $request->target_date;

        if ($request->clock_in) {
            Attendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $targetDate, 'type' => 'clock_in'],
                ['time' => $targetDate . ' ' . $request->clock_in]
            );
        }
        if ($request->clock_out) {
            Attendance::updateOrCreate(
                ['user_id' => $userId, 'date' => $targetDate, 'type' => 'clock_out'],
                ['time' => $targetDate . ' ' . $request->clock_out]
            );
        }

        Attendance::where('user_id', $userId)
            ->where('date', $targetDate)
            ->whereIn('type', ['break_in', 'break_out'])
            ->delete();

        if (!empty($request->break_in) && !empty($request->break_out)) {
            Attendance::create([
                'user_id' => $userId,
                'date'    => $targetDate,
                'time'    => $targetDate . ' ' . $request->break_in,
                'type'    => 'break_in'
            ]);
            Attendance::create([
                'user_id' => $userId,
                'date'    => $targetDate,
                'time'    => $targetDate . ' ' . $request->break_out,
                'type'    => 'break_out'
            ]);
        }

        Attendance::updateOrCreate(
            ['user_id' => $userId, 'date' => $targetDate, 'type' => 'clock_in'],
            ['note' => $request->note]
        );

        RequestModel::updateOrCreate(
            [
                'user_id'     => $userId,
                'target_date' => $targetDate,
            ],
            [
                'reason'      => $request->reason,
                'applied_date'=> now(),
                'status'      => 'updated',
                'clock_in'    => $request->clock_in,
                'clock_out'   => $request->clock_out,
                'break_in'    => $request->break_in,
                'break_out'   => $request->break_out,
            ]
        );

        return redirect()->back()->with('status', 'updated');
    }
}
