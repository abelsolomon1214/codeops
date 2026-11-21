# Addis Eats API (`addis-eats-api`)

RESTful backend API for Addis Eats, a food-delivery platform in Addis Ababa. Built with Laravel 11 as part of the CodeOps Module 4 Backend Capstone.

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

# 3. Create SQLite database file (if not present) and run migrations with seed data
php artisan migrate --seed

# 4. Start the local development server
php artisan serve
```

## Day 55 Foundation (Core API Build)

The initial Day 55 capstone build establishes the core architecture of `addis-eats-api`:
- **RESTful Dish Resource (`/api/v1/dishes`):** Full CRUD endpoints (`index`, `store`, `show`, `update`, `destroy`) wired with `Route::apiResource` and numeric ID constraint (`->whereNumber('dish')`), returning `201 Created` with a `Location` header on creation and `204 No Content` with soft deletes on removal.
- **Integer *Santim* Currency Model:** Stores all dish prices as integer *santim* (`100 santim = 1.00 ETB`) to avoid floating-point rounding errors, formatted into two-decimal `price_etb` strings at the resource layer.
- **Strict API Resource Shaping:** `DishResource` and `RestaurantResource` control the public JSON contract and strip internal database columns (`cost_price`, `supplier_note`, `deleted_at`, and model timestamps).
- **Composable Base Query Filters:** Supports combining `?area=`, `?max_price=`, and `?fasting=` on `GET /api/v1/dishes` using Eloquent `->when()`.
- **Slug-Bound Restaurant Lookup:** Exposes `GET /api/v1/restaurants` and `GET /api/v1/restaurants/{restaurant:slug}` (e.g., `/api/v1/restaurants/yod-abyssinia`) via custom Route Model Binding on `slug`.

## Enhancements Added (Post-Review Upgrades)

Built on top of the Day 55 foundation to strengthen input validation, querying, and restaurant navigation:
- **Request Validation (`422` Protection):** Added `$request->validate()` in `DishController::store()` and `update()`, enforcing integer *santim* minimums (`min:100` with a custom error message), string length bounds, boolean casting, and `sometimes|required` rules for partial `PATCH` updates.
- **Extended Dish Search & Filtering:** Added `?restaurant=` exact filtering and `?search=` (`LIKE` partial name matching) alongside the base `area`, `max_price`, and `fasting` filters.
- **Allow-Listed Sorting & Clamped Pagination:** Added safe `match` sorting (`?sort=price_desc`, `?sort=name`, and default `?sort=price` ascending) plus dynamic `?per_page=` clamped between `1` and `50`, preserving active query parameters via `withQueryString()`.
- **Restaurant Area Filter & Menu Endpoint:** Added `?area=` filtering to `GET /api/v1/restaurants` and introduced `GET /api/v1/restaurants/{restaurant:slug}/dishes` to fetch all dishes for a specific restaurant sorted by price.

## API Endpoints (`/api/v1`)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/dishes` | List dishes with filters, sorting, and pagination |
| `POST` | `/api/v1/dishes` | Validate and create a new dish (`201` + `Location` header) |
| `GET` | `/api/v1/dishes/{dish}` | Retrieve a single dish by numeric ID |
| `PATCH` | `/api/v1/dishes/{dish}` | Partially update a dish with validation |
| `DELETE` | `/api/v1/dishes/{dish}` | Soft-delete a dish (`204 No Content`) |
| `GET` | `/api/v1/restaurants` | List restaurants (supports `?area=`) |
| `GET` | `/api/v1/restaurants/{restaurant:slug}` | Retrieve a restaurant by slug |
| `GET` | `/api/v1/restaurants/{restaurant:slug}/dishes` | List all dishes for a restaurant by slug |