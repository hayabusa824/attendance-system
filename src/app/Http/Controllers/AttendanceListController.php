<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $dates = [];
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $attendances = Attendance::where('user_id', $userId)
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
                'id' => optional($clockIn)->id, // ← 詳細リンク用IDを追加
                'date' => $date->copy(),
                'date_label' => $date->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$date->dayOfWeek] . ')',
                'clock_in' => optional($clockIn)->time ? Carbon::parse($clockIn->time)->format('H:i') : '',
                'clock_out' => optional($clockOut)->time ? Carbon::parse($clockOut->time)->format('H:i') : '',
                'break' => $breakMinutes > 0 ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                'total' => $totalMinutes > 0 ? floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
            ];
        }

        return view('attendanceList', compact('records', 'year', 'month'));
    }
}
