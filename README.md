# Country Currency & Exchange API

A RESTful API built with Laravel 11 that fetches, caches, and manages country data with exchange rates and estimated GDP.

## Setup Instructions

1. Clone the repo: `git clone git@github.com:samthatcode/country-api.git`
2. Install dependencies: `composer install`
3. Copy env: `cp .env.example .env` and configure DB.
4. Generate key: `php artisan key:generate`
5. Migrate: `php artisan migrate`
6. Link storage: `php artisan storage:link`
7. Serve: `php artisan serve`

## Endpoints

-   POST https://country-api-spuv.onrender.com/api/v1/countries/refresh: Refresh data from external APIs.
-   GET https://country-api-spuv.onrender.com/api/v1/countries: List countries (filters: ?region=Africa, ?currency=NGN, ?sort=gdp_desc)
-   GET https://country-api-spuv.onrender.com/api/v1/countries/:name: Get country by name.
-   DELETE https://country-api-spuv.onrender.com/api/v1/countries/:name: Delete country by name.
-   GET https://country-api-spuv.onrender.com/api/v1/status: Get status.
-   GET https://country-api-spuv.onrender.com/api/v1/countries/image: Get summary image.

## Dependencies

-   Laravel 11
-   ImageManager

## Environment Variables

-   DB_CONNECTION, DB_HOST, etc. for MySQL.

## Notes

-   Place a font file at public/fonts/arial.ttf for image generation.
-   Test endpoints using Postman.
-   Hosted on [your-hosting-platform] (e.g., Railway).
