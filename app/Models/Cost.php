<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;

    public const SIZE_PER_STUDY_IN_MB = 10;
    public const RAM_PER_STUDY_IN_MB = 0.50;

    public const STORAGE_COST_PER_GB_PER_MONTH_USD = 0.10;
    public const RAM_COST_PER_GB_PER_DAY_USD = 0.13272;

    protected $fillable = [
        'forecast_id',
        'month',
        'year',
        'total_studies',
        'total_cost',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'total_cost' => 'double',
    ];
}
