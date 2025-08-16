<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class StaffAttendanceController extends Controller
{
    public function show(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $records = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $attendances = Attendance::where('user_id', $id)
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
                'date' => $date->copy(),
                'date_label' => $date->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$date->dayOfWeek] . ')',
                'clock_in' => optional($clockIn)->time ? Carbon::parse($clockIn->time)->format('H:i') : '',
                'clock_out' => optional($clockOut)->time ? Carbon::parse($clockOut->time)->format('H:i') : '',
                'break' => $breakMinutes > 0 ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                'total' => $totalMinutes > 0 ? floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
            ];
        }

        return view('staffAttendance', compact('user', 'records', 'year', 'month'));
    }

    public function exportCsv(Request $request)
    {
        $userId = $request->input('user_id');
        $year = $request->input('year');
        $month = $request->input('month');

        $user = User::findOrFail($userId);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $records = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->orderBy('time')
            ->get()
            ->groupBy('date');

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
        $csvData = [];

        foreach ($records as $date => $dailyRecords) {
            $clockInRecord = $dailyRecords->where('type', 'clock_in')->first();
            $clockIn = $clockInRecord ? $clockInRecord->time : null;

            $clockOutRecord = $dailyRecords->where('type', 'clock_out')->last();
            $clockOut = $clockOutRecord ? $clockOutRecord->time : null;

            $breakMinutes = 0;
            $in = null;
            foreach ($dailyRecords as $r) {
                if ($r->type === 'break_in') {
                    $in = Carbon::parse($r->time);
                } elseif ($r->type === 'break_out' && $in) {
                    $out = Carbon::parse($r->time);
                    $breakMinutes += $in->diffInMinutes($out);
                    $in = null;
                }
            }

            $totalMinutes = 0;
            if ($clockIn && $clockOut) {
                $totalMinutes = Carbon::parse($clockIn)->diffInMinutes(Carbon::parse($clockOut)) - $breakMinutes;
            }

            $csvData[] = [
                $date,
                $clockIn ? Carbon::parse($clockIn)->format('H:i') : '',
                $clockOut ? Carbon::parse($clockOut)->format('H:i') : '',
                gmdate('H:i', $breakMinutes * 60),
                gmdate('H:i', max(0, $totalMinutes) * 60),
            ];
        }

        $filename = "attendance_{$user->id}_{$year}{$month}.csv";

        $handle = fopen('php://temp', 'r+');
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, $csvHeader);

        foreach ($csvData as $line) {
            fputcsv($handle, $line);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}

