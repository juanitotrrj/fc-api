<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;

class ProcessForecast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $studiesPerDay;
    protected $growthPerMonth;
    protected $numberOfMonths;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        int $id,
        int $studiesPerDay,
        float $growthPerMonth,
        int $numberOfMonths
    ) {
        $this->id = $id;
        $this->studiesPerDay = $studiesPerDay;
        $this->growthPerMonth = $growthPerMonth;
        $this->numberOfMonths = $numberOfMonths;
    }

    /**
     * Define the number of studies per month and days per month in order to schedule
     * a Job to create Costs record, in a given Forecast
     *
     * @return void
     */
    public function handle()
    {
        $generationPeriod = $this->getGenerationPeriod();

        $lastTotalStudiesCount = null;
        foreach ($generationPeriod as $firstDayOfMonth) {
            $numberOfStudiesOnThisMonth = 0;
            $numberOfDaysInThisMonth = $firstDayOfMonth->daysInMonth;

            if (empty($lastTotalStudiesCount)) {
                $numberOfStudiesOnThisMonth = strval($this->studiesPerDay * $numberOfDaysInThisMonth);
            } else {
                $numberOfStudiesOnThisMonth = bcadd(
                    $lastTotalStudiesCount,
                    bcmul($lastTotalStudiesCount, strval($this->growthPerMonth), 50)
                );
            }

            $lastTotalStudiesCount = $numberOfStudiesOnThisMonth;

            ProcessCost::dispatch(
                $this->id,
                $numberOfStudiesOnThisMonth,
                $numberOfDaysInThisMonth,
                $firstDayOfMonth->format('m'),
                $firstDayOfMonth->format('Y')
            );
        }
    }

    /**
     * Generate the monthly Periods by the number of months to generate
     *
     * @return Carbon\CarbonPeriod
     */
    private function getGenerationPeriod()
    {
        $monthsUntilEnd = $this->numberOfMonths - 1; // Includes current month
        $generationStartDate = CarbonImmutable::parse("first day of this month");
        $generationEndDate = $generationStartDate->addMonths($monthsUntilEnd)->endOfMonth();
        $generationPeriod = CarbonInterval::months(1)->toPeriod(
            $generationStartDate->format('Y-m-d'),
            $generationEndDate->format('Y-m-d')
        );

        return $generationPeriod;
    }
}
