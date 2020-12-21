# Forecast API

## Installation
1. Clone this repo
   ```bash
   git clone git@github.com:juanitotrrj/fc-api.git
   cd fc-api
   ```
2. Provision/Up Laravel and Sail
   ```bash
   composer install
   vendor/bin/sail up
   ```
3. Generate Swagger/OpenAPI documentation
   ```bash
   vendor/bin/sail artisan l5-swagger:generate
   ```
4. Run queue worker
   ```bash
   vendor/bin/sail artisan queue:work
   ```

## Usage
* **View the API Explorer at: http://localhost/api/documentation**
* To generate an estimate:
   1. Specify the parameters in `POST /api/v1/forecasts`
   1. Call `POST /api/v1/forecasts`
   1. List created Forecast in `GET /api/v1/forecasts`
      * Apply pagination params if you have to, but there are already default values for pagination
* To view generated estimates:
   1. List created Forecast in `GET /api/v1/forecasts`
      * Apply pagination params if you have to, but there are already default values for pagination
   1. List the computed Costs, using the `forecast_id`:
      1. Call `GET /api/v1/forecasts/{id}/costs`
         * Apply pagination params if you have to, but there are already default values for pagination
