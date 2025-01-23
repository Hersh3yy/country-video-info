# Bravoure Backend Challenge

RESTful API combining YouTube trending videos and Wikipedia data for European countries.

## Setup

1. Clone repository
2. Copy `.env.example` to `.env`
3. Add YouTube API key: `YOUTUBE_API_KEY=your_key`
4. Run:
```bash
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan telescope:install
docker-compose exec app php artisan l5-swagger:generate
```

## API Documentation

-   OpenAPI/Swagger UI: `/api/documentation`
-   Telescope Debugging: `/telescope`

## Endpoints

`GET /api/country-videos`

Query parameters:

-   `countries[]`: Country codes (default: GB, NL, DE, FR, ES, IT, GR)
-   `page`: Page number for pagination
-   `per_page`: Results per page