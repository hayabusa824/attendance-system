<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now()->startOfDay();

        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $users = User::where('id', '!=', auth()->id())->get();
        $records = [];

        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->orderBy('time')
                ->get();

            $clockIn = $attendances->firstWhere('type', 'clock_in');
            $clockOut = $attendances->firstWhere('type', 'clock_out');

            $breakMinutes = 0;
            $in = null;
            foreach ($attendances as $record) {
                if ($record->type === 'break_in') {
                    $in = Carbon::parse($record->time);
                } elseif ($record->type === 'break_out' && $in) {
                    $out = Carbon::parse($record->time);
                    $breakMinutes += $in->diffInMinutes($out);
                    $in = null;
                }
            }

            $totalMinutes = 0;
            if ($clockIn && $clockOut) {
                $totalMinutes = Carbon::parse($clockIn->time)->diffInMinutes(Carbon::parse($clockOut->time)) - $breakMinutes;
            }

            $records[] = [
                'id' => optional($clockIn)->id,
                'name' => $user->name,
                'clock_in' => optional($clockIn)->time ? Carbon::parse($clockIn->time)->format('H:i') : '',
                'clock_out' => optional($clockOut)->time ? Carbon::parse($clockOut->time)->format('H:i') : '',
                'break' => $breakMinutes > 0 ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                'total' => $totalMinutes > 0 ? floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
            ];
        }

        return view('AdminAttendance', compact('records', 'date', 'prevDate', 'nextDate', 'year', 'month'));
    }
}