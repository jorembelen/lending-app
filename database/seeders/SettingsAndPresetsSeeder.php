<?php

namespace Database\Seeders;

use App\Models\HolidayCalendar;
use App\Models\RatePreset;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SettingsAndPresetsSeeder extends Seeder
{
    public function run(): void
    {
        // Global settings
        Setting::set('weekly_off_day', 0);           // 0 = Sunday
        Setting::set('lockout_attempt_threshold', 5);
        Setting::set('default_off_day_label', 'Sunday');

        // Rate presets
        RatePreset::firstOrCreate(
            ['name' => 'Standard 20/1000/60'],
            [
                'rate_per_1000' => 20.00,
                'term_days'     => 60,
                'is_default'    => true,
                'is_active'     => true,
            ]
        );

        RatePreset::firstOrCreate(
            ['name' => 'Express 25/1000/30'],
            [
                'rate_per_1000' => 25.00,
                'term_days'     => 30,
                'is_default'    => false,
                'is_active'     => true,
            ]
        );

        RatePreset::firstOrCreate(
            ['name' => 'Extended 18/1000/90'],
            [
                'rate_per_1000' => 18.00,
                'term_days'     => 90,
                'is_default'    => false,
                'is_active'     => true,
            ]
        );

        // Holiday calendar — past ~6 months and next ~2 months relative to 2026-06-22
        $holidays = [
            ['date' => '2025-12-25', 'label' => 'Christmas Day'],
            ['date' => '2025-12-30', 'label' => 'Rizal Day'],
            ['date' => '2025-12-31', 'label' => "New Year's Eve"],
            ['date' => '2026-01-01', 'label' => "New Year's Day"],
            ['date' => '2026-02-25', 'label' => 'EDSA People Power Revolution Anniversary'],
            ['date' => '2026-03-30', 'label' => 'Araw ng Kagitingan'],
            ['date' => '2026-04-02', 'label' => 'Maundy Thursday'],
            ['date' => '2026-04-03', 'label' => 'Good Friday'],
            ['date' => '2026-05-01', 'label' => 'Labor Day'],
            ['date' => '2026-06-12', 'label' => 'Independence Day'],
            ['date' => '2026-06-19', 'label' => 'Eid al-Adha'],
            ['date' => '2026-07-04', 'label' => 'Araw ng Kagitingan (special)'],
            ['date' => '2026-08-31', 'label' => 'National Heroes Day'],
        ];

        foreach ($holidays as $h) {
            HolidayCalendar::firstOrCreate(['date' => $h['date']], ['label' => $h['label']]);
        }

        $this->command->info('SettingsAndPresetsSeeder: 3 settings, 3 rate presets, ' . count($holidays) . ' holidays seeded.');
    }
}
