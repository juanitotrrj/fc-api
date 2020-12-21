<?php

namespace App\Services;

use App\Models\Forecast;
use App\Jobs\ProcessForecast;

class ForecastService
{
    public function index(int $page = 1, int $perPage = 5)
    {
        $query = Forecast::query();
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function get($id)
    {
        return Forecast::find($id);
    }

    public function create(array $attributes)
    {
        $forecast = Forecast::create($attributes);

        ProcessForecast::dispatch(
            $forecast->id,
            $forecast->studies_per_day,
            $forecast->growth_per_month,
            $forecast->number_of_months,
        );

        return $forecast;
    }
}
