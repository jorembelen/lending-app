<?php

namespace App\Services;

use App\Models\HolidayCalendar;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LoanScheduleService
{
    private int $offDayOfWeek;
    private Collection $holidays;

    public function __construct()
    {
        // 0 = Sunday, 6 = Saturday (Carbon convention)
        $this->offDayOfWeek = (int) Setting::get('weekly_off_day', 0);
        $this->holidays = HolidayCalendar::pluck('date')->map(
            fn ($d) => Carbon::parse($d)->toDateString()
        );
    }

    /**
     * Generate exactly $termDays valid collection dates starting the day after $disbursedAt.
     *
     * @return Collection<Carbon>
     */
    public function generateSchedule(Carbon $disbursedAt, int $termDays): Collection
    {
        $dates = collect();
        $cursor = $disbursedAt->copy()->addDay();

        while ($dates->count() < $termDays) {
            if ($this->isCollectionDay($cursor)) {
                $dates->push($cursor->copy());
            }
            $cursor->addDay();
        }

        return $dates;
    }

    public function isCollectionDay(Carbon $date): bool
    {
        if ($date->dayOfWeek === $this->offDayOfWeek) {
            return false;
        }

        if ($this->holidays->contains($date->toDateString())) {
            return false;
        }

        return true;
    }
}
