<?php

namespace App\Services;

use App\Models\Cost;

class CostService
{
    /**
     * List all Costs per Forecast
     *
     * @param int $forecastId Forecast ID
     * @param int $page Page number
     * @param int $perPage Number of results per page
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function index(int $forecastId, int $page = 1, int $perPage = 5)
    {
        $query = Cost::where(['forecast_id' => $forecastId]);
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Create cost
     *
     * @param array $attributes
     * @return App\Models\Cost
     */
    public function create(array $attributes)
    {
        return Cost::create($attributes);
    }

    /**
     * Get total cost in a month
     *
     * @param int $studiesCount Total number of studies in a month
     * @param int $daysInMonth Total number of days in a month
     * @return string Total cost of resources in this month
     */
    public function getTotalCost(int $studiesCount, int $daysInMonth)
    {
        $totalStorageCost = $this->computeStorageCostForThisMonth($studiesCount);
        $totalRamCost = $this->computeRamCostForThisMonth($studiesCount, $daysInMonth);

        return bcadd($totalStorageCost, $totalRamCost, 50);
    }

    /**
     * Determine the total cost of storage in a month, by the number of total studies in a month
     *
     * @param int $studiesCount Total number of studies in a month
     * @return string Total cost of storage resources in this month
     */
    public function computeStorageCostForThisMonth(int $studiesCount)
    {
        $totalStorageSizeInMB = bcmul(
            strval($studiesCount),
            strval(Cost::SIZE_PER_STUDY_IN_MB),
            50
        );

        return bcmul(
            bcdiv($totalStorageSizeInMB, "1000.00", 50),
            strval(Cost::STORAGE_COST_PER_GB_PER_MONTH_USD),
            50
        );
    }

    /**
     * Determine the total cost of RAM in a month, by the number of total studies in a month and
     * number of days in a month
     *
     * @param int $studiesCount Total number of studies in a month
     * @param int $daysInMonth Total number of days in a month
     * @return string Total cost of RAM resources in this month
     */
    public function computeRamCostForThisMonth(int $studiesCount, int $daysInMonth)
    {
        $costPerGBPerMonth = bcmul(strval($daysInMonth), strval(Cost::RAM_COST_PER_GB_PER_DAY_USD), 50);
        $totalRamSizeInMBThisMonth = bcmul(
            strval($studiesCount),
            strval(Cost::RAM_PER_STUDY_IN_MB),
            50
        );

        return bcmul(
            bcdiv($totalRamSizeInMBThisMonth, "1000.00", 50),
            $costPerGBPerMonth,
            50
        );
    }
}
