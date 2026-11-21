# Addis Eats API (`addis-eats-api`)

RESTful backend API for Addis Eats, a food-delivery platform in Addis Ababa, built with Laravel 11 as part of the CodeOps Module 4 Backend Capstone.

## Tech Stack
- PHP 8.2+ / Laravel 11
- SQLite (local development & testing)
- Eloquent ORM & API Resources

## Installation & Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Create environment file and generate application key
cp .env.example .env
php artisan key:generate

# 3. Run database migrations and seed sample restaurants and dishes
php artisan migrate --seed

# 4. Start the local development server
php artisan serve
```

## Architecture & Conventions

- **Versioned `Api\V1` Controllers & Named Routes:** All controllers live under `App\Http\Controllers\Api\V1` with `v1.` route names and parameter constraints (`[0-9]+` for dish IDs, `[a-z0-9-]+` for restaurant slugs).
- **Unified `ApiResponse` Envelope:** Every JSON response goes through `App\Http\Responses\ApiResponse` (`ok()`, `created()`, `error()`) so responses consistently use `{"data": ..., "meta": ...}` or `{"error": {"code", "message", "meta"}}`.
- **Integer *Santim* Currency:** Prices are stored in integer *santim* (`100 santim = 1.00 ETB`) and formatted into `price_etb` strings inside `DishResource`.
- **Eloquent Query Scopes & Whitelisted Sorting:** Reusable filters (`scopeInArea`, `scopeUnder`, `scopeFasting`) live on `App\Models\Dish`, and `?sort=` is checked against an explicit `SORTABLE` whitelist (`-price` for descending).
- **Slug-Bound Restaurants & Shallow-Nested Dishes:** Restaurants are bound on `slug`, dishes are shallow-nested under `restaurants.dishes`, and `?include=dishes` uses `withCount('dishes')`, `with('dishes')`, `whenCounted()`, and `whenLoaded()` to prevent $N+1$ queries.

## Query-String Convention

| Concern | Shape | Example |
| :--- | :--- | :--- |
| **Filters** | One parameter per field | `?area=Bole&fasting=1` |
| **Search** | `q` — one free-text parameter | `?q=doro` |
| **Sort** | `sort`, minus prefix for descending | `?sort=-price` |
| **Paging** | `page` and `per_page` (Day 57) | `?page=2&per_page=15` |
| **Includes** | `include`, comma separated | `?include=dishes` |

## API Endpoints (`/api/v1`)

| Method | Endpoint | Route Name | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/dishes` | `v1.dishes.index` | List dishes with scopes (`area`, `max_price`, `fasting`), `?q=`, and whitelisted `?sort=` |
| `POST` | `/api/v1/dishes` | `v1.dishes.store` | Create a dish (`201 Created` + `Location` header) |
| `GET` | `/api/v1/dishes/{dish}` | `v1.dishes.show` | Retrieve a single dish by numeric ID |
| `PATCH` | `/api/v1/dishes/{dish}` | `v1.dishes.update` | Partially update a dish |
| `DELETE` | `/api/v1/dishes/{dish}` | `v1.dishes.destroy` | Soft-delete a dish (`204 No Content`) |
| `GET` | `/api/v1/restaurants` | `v1.restaurants.index` | List restaurants (`?area=`, `?q=`, `?sort=`, `?include=dishes`) |
| `POST` | `/api/v1/restaurants` | `v1.restaurants.store` | Create a restaurant (`201 Created` + `Location` header) |
| `GET` | `/api/v1/restaurants/{restaurant}` | `v1.restaurants.show` | Retrieve a restaurant by slug (`?include=dishes`) |
| `PATCH` | `/api/v1/restaurants/{restaurant}` | `v1.restaurants.update` | Partially update a restaurant by slug |
| `DELETE` | `/api/v1/restaurants/{restaurant}` | `v1.restaurants.destroy` | Delete a restaurant by slug (`204 No Content`) |
| `GET` | `/api/v1/restaurants/{restaurant}/dishes` | `v1.restaurants.dishes.index` | List dishes for a restaurant by slug |
| `POST` | `/api/v1/restaurants/{restaurant}/dishes` | `v1.restaurants.dishes.store` | Create a dish under a restaurant by slug |
