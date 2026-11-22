# Addis Eats API (`addis-eats-api`)

RESTful backend API for Addis Eats, a food-delivery platform in Addis Ababa, built with Laravel 11 as part of the CodeOps Module 4 Backend Capstone.

## Tech Stack
- PHP 8.2+ / Laravel 11
- SQLite (local development & testing)
- Eloquent ORM, Form Requests, Domain Services, API Resources & Offset Pagination

## Installation & Setup

    # 1. Install PHP dependencies
    composer install

    # 2. Create environment file and generate application key
    cp .env.example .env
    php artisan key:generate

    # 3. Run database migrations and seed sample users, restaurants, dishes, and orders
    php artisan migrate --seed

    # 4. Run the automated test suite
    php artisan test

    # 5. Start the local development server
    php artisan serve

## Architecture & Conventions (Day 55 – Day 58)

- **Versioned `routes/api_v1.php` & Thin `Api\V1` Controllers:** `routes/api.php` loads `routes/api_v1.php` under `prefix('v1')->name('v1.')`. Controllers contain zero inline write validation, zero `$request->all()` / `$request->only()` calls, and zero `try/catch` blocks.
- **Four-Layer Validation & Domain Defense:**
  1. **Route Constraints:** `whereNumber('order')` and slug regex constraints reject malformed path parameters early.
  2. **Dedicated Form Requests (`App\Http\Requests\V1`):** `StoreDishRequest`, `UpdateDishRequest`, `StoreRestaurantRequest`, `UpdateRestaurantRequest`, `StoreRestaurantDishRequest`, `StoreOrderRequest`, and `UpdateOrderStatusRequest` normalize inputs in `prepareForValidation()`, validate types/ranges/database existence, and whitelist fields via `$request->validated()`.
  3. **Custom Rule & Service Layer (`App\Rules\TeleBirrNumber`, `App\Services\OrderService`):** Enforces normalized `09XXXXXXXX` TeleBirr numbers, atomic order + `order_lines` creation in `DB::transaction()`, and order state-machine transitions (`409 Conflict` via `IllegalTransitionException`).
  4. **Database Constraints:** Foreign keys (`restrictOnDelete()` on ordered dishes, `cascadeOnDelete()` on `order_lines`), composite indexes, and `NOT NULL` guards.
- **Unified Success & Error Envelopes:** Single items and mutations use `App\Http\Responses\ApiResponse` (`ok()`, `created()`, `error()`), list endpoints return paginated `JsonResource` collections (`data`, `links`, `meta`) with `withQueryString()`, and all exceptions render through `bootstrap/app.php` into `{ "error": { "code", "message", "meta" } }`.
- **Integer *Santim* Currency:** Prices, line totals, delivery fees, and order totals are stored in integer *santim* (`100 santim = 1.00 ETB`) and formatted into `*_etb` strings inside API Resources.

## Error Catalogue (`ERRORS.md`)

All `/api/v1` error responses share one envelope (`{ "error": { "code", "message", "meta" } }`) registered in `bootstrap/app.php` and documented in [`ERRORS.md`](ERRORS.md):

| Exception | `error.code` | HTTP Status | When it is emitted |
| :--- | :--- | :--- | :--- |
| `ValidationException` | `validation_failed` | `422` | A Form Request or query validator rejected invalid input fields |
| `OutsideZoneException` | `outside_delivery_zone` | `422` | A domain check rejected an area outside `config('addiseats.zones')` |
| `BadRequestHttpException` | `bad_request` | `400` | The HTTP request payload or syntax is malformed |
| `AuthenticationException` | `unauthenticated` | `401` | Missing, invalid, or expired bearer token |
| `AuthorizationException` | `forbidden` | `403` | Authenticated user lacks permission for the requested action |
| `ModelNotFoundException` | `not_found` | `404` | Route model binding could not find the requested resource ID or slug |
| `NotFoundHttpException` | `route_not_found` | `404` | No API route matched the requested URI |
| `MethodNotAllowedHttpException` | `method_not_allowed` | `405` | The URI exists, but the HTTP verb is not supported on that route |
| `IllegalTransitionException` | `illegal_transition` | `409` | An order status transition is forbidden in the order's current state |
| `ThrottleRequestsException` | `rate_limited` | `429` | Client exceeded the endpoint rate limit |
| `Throwable` (fallback) | `server_error` | `500` | Unexpected internal failure; full details logged server-side with `meta.ref` |

## The Filter Contract

Unknown query parameters are ignored rather than rejected. All enforced query parameters across `/api/v1` list endpoints follow this contract:

| Parameter | Type | Applies to | Default | Notes |
| :--- | :--- | :--- | :--- | :--- |
| `area` | string | `dishes`, `restaurants`, `orders` | none | Must be a configured delivery zone (`Bole`, `Kazanchis`, `Piassa`, `Megenagna`) |
| `max_price` | integer, santim | `dishes` | none | Inclusive upper bound (`price <= max_price`) |
| `fasting` | boolean | `dishes` | none | `1`, `true`, `on`, and `yes` all accepted |
| `status` | string | `orders` | none | Must be a valid `OrderStatus` (`placed`, `confirmed`, `preparing`, `dispatched`, `delivered`, `cancelled`) |
| `q` | string | `dishes`, `restaurants` | none | Partial match on `name` |
| `sort` | string | `dishes`, `restaurants` | `price` (dishes) / `name` (restaurants) | Whitelisted fields (`name`, `price`/`area`, `created_at`); `-` prefix for descending |
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
| `GET` | `/api/v1/orders` | `v1.orders.index` | List paginated orders (`area`, `status`, `page`, `per_page`) |
| `POST` | `/api/v1/orders` | `v1.orders.store` | Place an order with `lines` (`201 Created` + `Location` header) |
| `GET` | `/api/v1/orders/{order}` | `v1.orders.show` | Retrieve a single order with its restaurant and `lines` |
| `PATCH` | `/api/v1/orders/{order}/status` | `v1.orders.status.update` | Transition order status (`409 Conflict` if illegal in current state) |

## Beyond REST: REST vs. GraphQL vs. gRPC

### REST (Representational State Transfer)
REST models the domain as nouns identified by URIs (`/api/v1/dishes`, `/api/v1/restaurants`, `/api/v1/orders`) manipulated through standard HTTP verbs (`GET`, `POST`, `PATCH`, `DELETE`) and status codes (`200`, `201`, `204`, `400`, `404`, `405`, `409`, `422`) over JSON. Its biggest strengths are ubiquity, browser and curl friendliness, rich ecosystem tooling, and native HTTP caching via CDNs and reverse proxies because every `GET` endpoint has a distinct, cacheable URL.

The primary cost of REST is fixed response shapes: when a screen needs data from multiple related resources, a client either makes several round trips or relies on explicit `?include=` flags, and different screens can suffer from over-fetching unused fields or under-fetching nested ones.

### GraphQL
GraphQL exposes a single HTTP endpoint (typically `POST /graphql`) backed by a strongly typed schema, allowing the client to send a query specifying the exact fields and nested relationships it needs in one round trip. It excels at mobile apps and complex frontends with many screen variations where minimizing payload size and avoiding multiple round trips over slow cellular networks is critical.

That flexibility comes at a real operational cost: because requests are usually `POST` queries to a single URL, standard HTTP and CDN caching is largely lost, and unbounded nested queries can trigger severe database load or $N+1$ problems unless protected by query-depth limits, complexity analysis, and DataLoader batching.

### gRPC
gRPC is a high-performance Remote Procedure Call (RPC) framework built on HTTP/2 that uses Protocol Buffers (`.proto` schemas) to serialize messages into compact binary payloads rather than human-readable JSON. It is strongest for internal microservice-to-microservice communication where ultra-low latency, strict compile-time contracts, and bidirectional streaming matter most.

Its main trade-off is that it is not browser-native—web frontends require a gRPC-Web proxy—and binary payloads cannot be inspected directly in a browser tab or tested casually with `curl` without generated client stubs and `.proto` definitions.

### Why Addis Eats Uses REST
For Addis Eats, REST is the right architectural choice: our clients are a React web frontend and a mobile application consuming a focused set of related resources (`restaurants`, `dishes`, `orders`, `users`), and HTTP caching in front of restaurant menus that change only a couple of times a day delivers far more value than arbitrary field-level query flexibility. Standard HTTP verbs, status codes, and URI versioning keep the API simple to debug, cache, document in OpenAPI, and maintain across a small team.
