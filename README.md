# Bravoure Backend Challenge

This is a RESTful API that combines YouTube and Wikipedia data for European countries. All one needs is docker to run this locally.

## Setup

1. Clone repository
2. Copy `.env.example` to `.env`
3. Add YouTube API key: `YOUTUBE_API_KEY=your_key`
4. Run:
```bash
docker-compose up -d
docker-compose exec app composer install
docker-compose exec app php artisan migrate
docker-compose exec app php artisan telescope:install


## Endpoints
GET /api/country-videos - Returns YouTube trending videos and Wikipedia data for specified European countries.

### Query parameters:
countries (optional): Array of country codes (default: GB, NL, DE, FR, ES, IT, GR)

Debug
Access Laravel Telescope at /telescope for debugging API requests and responses.