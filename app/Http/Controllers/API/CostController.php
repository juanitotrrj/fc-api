<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\CostService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class CostController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/forecasts/{id}/costs",
     *      summary="Get list of Costs per Month",
     *      tags={"Costs"},
     *      description="Returns list of Costs per Month in a Forecast",
     *      operationId="",
     *      @OA\Parameter(
     *          name="id",
     *          description="Forecast ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
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
     *                         "forecast_id": 1,
     *                         "month": 1,
     *                         "year": 2020,
     *                         "total_studies": 31000,
     *                         "total_cost": 94.77,
     *                         "created_at": "2020-12-13 18:21:22",
     *                         "updated_at": "2020-12-13 18:21:22"
     *                     },
     *                 },
     *                 "from": 1,
     *                 "last_page": 12,
     *                 "per_page": 1,
     *                 "to": 1,
     *                 "total": 12
     *             }
     *          )
     *       ),
     *       @OA\Response(
     *          response=Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(
     *             example={
     *                 "errors": {
     *                     "forecast_id": {
     *                          "The forecast id must be an integer.",
     *                     },
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
    public function index(Request $request, $forecastId)
    {
        $attributes = $request->all();
        $attributes['forecast_id'] = $forecastId;

        $validator = Validator::make($attributes, [
            'forecast_id' => 'required|integer|exists:forecasts,id',
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

        return Arr::only((new CostService())->index($forecastId, $page, $perPage)->toArray(), [
            "current_page",
            "per_page",
            "total",
            "from",
            "to",
            "last_page",
            "data",
        ]);
    }
}
