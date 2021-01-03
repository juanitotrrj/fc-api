<?php

namespace App\Services;

use App\Models\Forecast;
use App\Jobs\ProcessForecast;
use Illuminate\Support\Arr;

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

    public function delete($forecastId)
    {
        $forecast = Forecast::find($forecastId);
        $forecast->costs()->delete();
        $forecast->delete();
    }

    public function update($forecastId, array $attributes)
    {
        $forecast = Forecast::find($forecastId);
        $forecast->costs()->delete();
        $forecast->update($attributes);
        $forecast->refresh();

        if (Arr::hasAny($array, ['studies_per_day','growth_per_month','number_of_months'])) {
            ProcessForecast::dispatch(
                $forecast->id,
                $forecast->studies_per_day,
                $forecast->growth_per_month,
                $forecast->number_of_months,
            );
        }

        return $forecast;
    }
}
