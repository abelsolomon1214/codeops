# Addis Eats API (`addis-eats-api`)

RESTful backend API for Addis Eats, a food-delivery platform in Addis Ababa, built with Laravel 11 as part of the CodeOps Module 4 Backend Capstone.

## Tech Stack
- PHP 8.2+ / Laravel 11
- SQLite (local development & testing)
- Eloquent ORM, API Resources & Offset Pagination

## Installation & Setup

    # 1. Install PHP dependencies
    composer install

    # 2. Create environment file and generate application key
    cp .env.example .env
    php artisan key:generate

    # 3. Run database migrations and seed sample users, restaurants, dishes, and orders
    php artisan migrate --seed

    # 4. Start the local development server
    php artisan serve

## Architecture & Conventions (Day 55 – Day 57)

- **Versioned `routes/api_v1.php` & `Api\V1` Controllers:** `routes/api.php` loads `routes/api_v1.php` under `prefix('v1')->name('v1.')`, keeping the HTTP contract versioned while sharing domain models, enums, and migrations.
- **Unified `ApiResponse` Envelope & Paginated Collections:** Single-item and mutation responses use `App\Http\Responses\ApiResponse` (`ok()`, `created()`, `error()`), while list endpoints return paginated `JsonResource` collections (`data`, `links`, `meta`) with `withQueryString()` preserving active filters.
- **Integer *Santim* Currency:** Prices and order totals are stored in integer *santim* (`100 santim = 1.00 ETB`) and formatted into `price_etb` strings inside `DishResource`.
- **Eloquent Query Scopes, Config Zones & Whitelisted Sorting:** Reusable filters (`scopeInArea`, `scopeUnder`, `scopeFasting`) live on `App\Models\Dish`, `?area=` validates against `config('addiseats.zones')`, and `?sort=` is checked against an explicit `SORTABLE` whitelist (`-price` for descending).
- **Orders Domain Foundation:** Includes `orders` migration, `App\Enums\OrderStatus` backed enum cast, `App\Models\Order` relations (`user()`, `restaurant()`), `OrderFactory`, and `OrderSeeder`.

## The Filter Contract

Unknown query parameters are ignored rather than rejected. All enforced query parameters across `/api/v1` list endpoints follow this contract:

| Parameter | Type | Applies to | Default | Notes |
| :--- | :--- | :--- | :--- | :--- |
| `area` | string | `dishes`, `restaurants` | none | Must be a configured delivery zone (`Bole`, `Kazanchis`, `Piassa`, `Megenagna`) |
| `max_price` | integer, santim | `dishes` | none | Inclusive upper bound (`price <= max_price`) |
| `fasting` | boolean | `dishes` | none | `1`, `true`, `on`, and `yes` all accepted |
| `q` | string | `dishes`, `restaurants` | none | Partial match on `name` |
| `sort` | string | all lists | `price` (dishes) / `name` (restaurants) | Whitelisted fields (`name`, `price`/`area`, `created_at`); `-` prefix for descending |
| `page` | integer | all lists | `1` | Out-of-range page returns an empty `data` array |
| `per_page` | integer | all lists | `15` | Clamped to minimum `1` and capped at `100` |

## API Endpoints (`/api/v1`)

| Method | Endpoint | Route Name | Description |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/dishes` | `v1.dishes.index` | List paginated dishes (`area`, `max_price`, `fasting`, `q`, `sort`, `page`, `per_page`) |
| `POST` | `/api/v1/dishes` | `v1.dishes.store` | Create a dish (`201 Created` + `Location` header) |
| `GET` | `/api/v1/dishes/{dish}` | `v1.dishes.show` | Retrieve a single dish by numeric ID |
| `PATCH` | `/api/v1/dishes/{dish}` | `v1.dishes.update` | Partially update a dish |
| `DELETE` | `/api/v1/dishes/{dish}` | `v1.dishes.destroy` | Soft-delete a dish (`204 No Content`) |
| `GET` | `/api/v1/restaurants` | `v1.restaurants.index` | List paginated restaurants (`area`, `q`, `sort`, `include=dishes`, `page`, `per_page`) |
| `POST` | `/api/v1/restaurants` | `v1.restaurants.store` | Create a restaurant (`201 Created` + `Location` header) |
| `GET` | `/api/v1/restaurants/{restaurant}` | `v1.restaurants.show` | Retrieve a restaurant by slug (`?include=dishes`) |
| `PATCH` | `/api/v1/restaurants/{restaurant}` | `v1.restaurants.update` | Partially update a restaurant by slug |
| `DELETE` | `/api/v1/restaurants/{restaurant}` | `v1.restaurants.destroy` | Delete a restaurant by slug (`204 No Content`) |
| `GET` | `/api/v1/restaurants/{restaurant}/dishes` | `v1.restaurants.dishes.index` | List paginated dishes for a restaurant by slug |
| `POST` | `/api/v1/restaurants/{restaurant}/dishes` | `v1.restaurants.dishes.store` | Create a dish under a restaurant by slug |

## Beyond REST: REST vs. GraphQL vs. gRPC

### REST (Representational State Transfer)
REST models the domain as nouns identified by URIs (`/api/v1/dishes`, `/api/v1/restaurants`) manipulated through standard HTTP verbs (`GET`, `POST`, `PATCH`, `DELETE`) and status codes (`200`, `201`, `204`, `404`, `409`, `422`) over JSON. Its biggest strengths are ubiquity, browser and curl friendliness, rich ecosystem tooling, and native HTTP caching via CDNs and reverse proxies because every `GET` endpoint has a distinct, cacheable URL.

The primary cost of REST is fixed response shapes: when a screen needs data from multiple related resources, a client either makes several round trips or relies on explicit `?include=` flags, and different screens can suffer from over-fetching unused fields or under-fetching nested ones.

### GraphQL
GraphQL exposes a single HTTP endpoint (typically `POST /graphql`) backed by a strongly typed schema, allowing the client to send a query specifying the exact fields and nested relationships it needs in one round trip. It excels at mobile apps and complex frontends with many screen variations where minimizing payload size and avoiding multiple round trips over slow cellular networks is critical.

That flexibility comes at a real operational cost: because requests are usually `POST` queries to a single URL, standard HTTP and CDN caching is largely lost, and unbounded nested queries can trigger severe database load or $N+1$ problems unless protected by query-depth limits, complexity analysis, and DataLoader batching.

### gRPC
gRPC is a high-performance Remote Procedure Call (RPC) framework built on HTTP/2 that uses Protocol Buffers (`.proto` schemas) to serialize messages into compact binary payloads rather than human-readable JSON. It is strongest for internal microservice-to-microservice communication where ultra-low latency, strict compile-time contracts, and bidirectional streaming matter most.

Its main trade-off is that it is not browser-native—web frontends require a gRPC-Web proxy—and binary payloads cannot be inspected directly in a browser tab or tested casually with `curl` without generated client stubs and `.proto` definitions.

### Why Addis Eats Uses REST
For Addis Eats, REST is the right architectural choice: our clients are a React web frontend and a mobile application consuming a focused set of related resources (`restaurants`, `dishes`, `orders`, `users`), and HTTP caching in front of restaurant menus that change only a couple of times a day delivers far more value than arbitrary field-level query flexibility. Standard HTTP verbs, status codes, and URI versioning keep the API simple to debug, cache, document in OpenAPI, and maintain across a small team.
