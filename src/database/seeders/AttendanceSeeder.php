<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = [3, 4, 5];

        foreach ($userIds as $userId) {
            $date = Carbon::now()->subDays(5);

            for ($i = 0; $i < 5; $i++) {
                Attendance::create([
                    'user_id' => $userId,
                    'date' => $date->copy()->addDays($i)->toDateString(),
                    'time' => $date->copy()->addDays($i)->setTime(9, 0)->toDateTimeString(),
                    'type' => 'clock_in',
                ]);

                Attendance::create([
                    'user_id' => $userId,
                    'date' => $date->copy()->addDays($i)->toDateString(),
                    'time' => $date->copy()->addDays($i)->setTime(12, 0)->toDateTimeString(),
                    'type' => 'break_in',
                ]);

                Attendance::create([
                    'user_id' => $userId,
                    'date' => $date->copy()->addDays($i)->toDateString(),
                    'time' => $date->copy()->addDays($i)->setTime(13, 0)->toDateTimeString(),
                    'type' => 'break_out',
                ]);

                Attendance::create([
                    'user_id' => $userId,
                    'date' => $date->copy()->addDays($i)->toDateString(),
                    'time' => $date->copy()->addDays($i)->setTime(18, 0)->toDateTimeString(),
                    'type' => 'clock_out',
                ]);
            }
        }
    }
}