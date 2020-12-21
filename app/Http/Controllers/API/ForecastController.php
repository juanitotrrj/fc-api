<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\ForecastService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class ForecastController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/forecasts",
     *      summary="Get list of Forecasts",
     *      tags={"Forecasts"},
     *      description="Returns list of created Forecasts",
     *      operationId="",
     *      @OA\Parameter(
     *          name="page",
     *          description="Target Page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Rows per page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=Symfony\Component\HttpFoundation\Response::HTTP_OK,
     *          description="successful operation",
     *          @OA\JsonContent(
     *             example={
     *                 "current_page": 1,
     *                 "data": {
     *                     {
     *                         "id": 1,
     *                         "name": "June 2020 to June 2021 Estimated Cost",
     *                         "studies_per_day": 1000,
     *                         "growth_per_month": 0.03,
     *                         "number_of_months": 12,
     *                         "created_at": "2020-12-13 18:21:22",
     *                         "updated_at": "2020-12-13 18:21:22"
     *                     },
     *                 },
     *                 "from": 1,
     *                 "last_page": 4,
     *                 "per_page": 1,
     *                 "to": 1,
     *                 "total": 4
     *             }
     *          )
     *       ),
     *       @OA\Response(
     *          response=Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(
     *             example={
     *                 "errors": {
     *                     "page": {
     *                          "The page must be an integer.",
     *                     },
     *                     "per_page": {
     *                          "The per page must be an integer.",
     *                     }
     *                 },
     *             }
     *          )
     *       ),
     *     )
     */
    public function index(Request $request)
    {
        $attributes = $request->all();

        $validator = Validator::make($attributes, [
            'page' => 'integer',
            'per_page' => 'integer',
        ]);

        if ($validator->fails()) {
            return response(
                [
                    'errors' => $validator->errors()->getMessages()
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $page = $attributes['page'] ?? self::DEFAULT_PAGE;
        $perPage = $attributes['per_page'] ?? self::DEFAULT_RESULTS_PER_PAGE;

        return Arr::only((new ForecastService())->index($page, $perPage)->toArray(), [
            "current_page",
            "per_page",
            "total",
            "from",
            "to",
            "last_page",
            "data",
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/forecasts",
     *     summary="Compute estimated costs",
     *     tags={"Forecasts"},
     *     description="Compute estimated costs based on studies per day, growth per month, and number of months specified.",
     *     operationId="",
     *     @OA\RequestBody(
     *         description="ForecastsPostRequest",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ForecastsPostRequest")
     *     ),
     *     @OA\Response(
     *         response=Symfony\Component\HttpFoundation\Response::HTTP_CREATED,
     *          description="successful operation",
     *          @OA\JsonContent(
     *             example={
     *                 "data": {
     *                     "id": 5,
     *                     "name": "December 2020 to November 2025 Estimated Cost",
     *                     "studies_per_day": 1000000,
     *                     "growth_per_month": 0.06,
     *                     "number_of_months": 60,
     *                     "created_at": "2020-12-13 18:21:22",
     *                     "updated_at": "2020-12-13 18:21:22"
     *                 }
     *             }
     *          )
     *       ),
     *     @OA\Response(
     *         response=Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
     *         description="Unprocessable Entity",
     *         @OA\JsonContent(
     *             example={
     *                 "message": "The given data was invalid.",
     *                 "errors": {
     *                     "name": {
     *                         "The name must be a string."
     *                     },
     *                     "studies_per_day": {
     *                         "The studies per day must be an integer."
     *                     },
     *                     "growth_per_month": {
     *                         "The growth per month must be a number."
     *                     },
     *                     "number_of_months": {
     *                         "The number of months must be an integer."
     *                     }
     *                 }
     *             }
     *         )
     *     ),
     * )
     *
     * @OA\Schema(
     *     type="object",
     *     schema="ForecastsPostRequest",
     *     title="ForecastsPostRequest",
     *     description="ForecastsPostRequest",
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         format="string",
     *         description="Forecast Name",
     *         title="forecast_name",
     *     ),
     *     @OA\Property(
     *         property="studies_per_day",
     *         type="integer",
     *         format="int64",
     *         description="Estimated number of studies per day",
     *         title="studies_per_day",
     *     ),
     *     @OA\Property(
     *         property="growth_per_month",
     *         type="number",
     *         format="double",
     *         description="Estimated growth percentage per month, expressed in decimals (e.g. 1.00, 0.50, etc)",
     *         title="growth_per_month",
     *     ),
     *     @OA\Property(
     *         property="number_of_months",
     *         type="integer",
     *         format="int64",
     *         description="Total number of Months to compute estimates for, starting with CURRENT MONTH",
     *         title="number_of_months",
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required|string|unique:forecasts,name',
            'studies_per_day' => 'required|integer',
            'growth_per_month' => 'required|numeric',
            'number_of_months' => 'required|integer',
        ]);

        $forecast = (new ForecastService())->create($attributes);

        return response(['data' => $forecast->toArray()], Response::HTTP_CREATED);
    }
}
