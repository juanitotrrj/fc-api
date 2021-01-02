<?php

namespace App\Jobs;

use App\Services\CostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $forecastId;
    protected $numberOfStudiesOnThisMonth;
    protected $daysInThisMonth;
    protected $month;
    protected $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        int $forecastId,
        string $numberOfStudiesOnThisMonth,
        int $daysInThisMonth,
        int $month,
        int $year
    ) {
        $this->forecastId = $forecastId;
        $this->numberOfStudiesOnThisMonth = $numberOfStudiesOnThisMonth;
        $this->daysInThisMonth = $daysInThisMonth;
        $this->month = $month;
        $this->year = $year;
    }

    /**
     * Given a number of studies per month, and days per month, create the Cost per month
     *
     * @return void
     */
    public function handle()
    {
        $costService = new CostService();
        $totalCost = $costService->getTotalCost(
            $this->numberOfStudiesOnThisMonth,
            $this->daysInThisMonth
        );

        $costService->create([
            'forecast_id' => $this->forecastId,
            'month' => $this->month,
            'year' => $this->year,
            'total_studies' => $this->numberOfStudiesOnThisMonth,
            'total_cost' => number_format($totalCost, 2, '.', ''),
        ]);
    }
}
