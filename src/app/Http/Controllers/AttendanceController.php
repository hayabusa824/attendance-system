<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{

    public function index()
    {
        $today = now()->toDateString();

        $records = Attendance::where('user_id', auth()->id())
            ->whereDate('date', $today)
            ->orderBy('time')
            ->get();

        $status = $records->last() ? $records->last()->type : null;

        if ($status === 'clock_out') {
            $statusLabel = '退勤済';
            $message = 'お疲れ様でした。';
        } elseif ($status === 'clock_in' || $status === 'break_out') {
            $statusLabel = '勤務中';
            $message = null;
        } elseif ($status === 'break_in') {
            $statusLabel = '休憩中';
            $message = null;
        } else {
            $statusLabel = '勤務外';
            $message = null;
        }

        $breakMinutes = 0;
        $in = null;

        foreach ($records as $r) {
            if ($r->type === 'break_in') {
                $in = Carbon::parse($r->time);
            } elseif ($r->type === 'break_out' && $in) {
                $out = Carbon::parse($r->time);
                $breakMinutes += $in->diffInMinutes($out);
                $in = null;
            }
        }

        return view('attendance', compact('statusLabel', 'message', 'breakMinutes'));
    }

    public function clockIn()
    {
        Attendance::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'type' => 'clock_in',
            'time' => now(),
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
    {
        Attendance::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'type' => 'clock_out',
            'time' => now(),
        ]);

        return redirect('/attendance');
    }

    public function breakIn()
    {
        Attendance::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'type' => 'break_in',
            'time' => now(),
        ]);

        return redirect('/attendance');
    }

    public function breakOut()
    {
        Attendance::create([
            'user_id' => auth()->id(),
            'date' => now()->toDateString(),
            'type' => 'break_out',
            'time' => now(),
        ]);

        return redirect('/attendance');
    }
}
