# Country Currency & Exchange API

A RESTful API built with Laravel 11 that fetches, caches, and manages country data with exchange rates and estimated GDP.

## Setup Instructions

1. Clone the repo: `git clone <repo-url>`
2. Install dependencies: `composer install`
3. Copy env: `cp .env.example .env` and configure DB.
4. Generate key: `php artisan key:generate`
5. Migrate: `php artisan migrate`
6. Link storage: `php artisan storage:link`
7. Serve: `php artisan serve`

## Endpoints

-   POST /api/countries/refresh: Refresh data from external APIs.
-   GET /api/countries: List countries (filters: ?region=Africa, ?currency=NGN, ?sort=gdp_desc)
-   GET /api/countries/:name: Get country by name.
-   DELETE /api/countries/:name: Delete country by name.
-   GET /api/status: Get status.
-   GET /api/countries/image: Get summary image.

## Dependencies

-   Laravel 11
-   Intervention/Image

## Environment Variables

-   DB_CONNECTION, DB_HOST, etc. for MySQL.

## Notes

-   Place a font file at public/fonts/arial.ttf for image generation.
-   Test endpoints using Postman.
-   Hosted on [your-hosting-platform] (e.g., Railway).
