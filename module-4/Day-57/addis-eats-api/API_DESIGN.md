# Addis Eats API Design (`API_DESIGN.md`)

**Base Path:** `/api/v1`  
**Format:** JSON over HTTP (`Accept: application/json`, `Content-Type: application/json`)  
**Currency:** Integer *santim* (`100 santim = 1.00 ETB`) for storage and input; formatted `price_etb` strings in read responses.  
**Richardson Maturity Level:** Level 2 (resources, HTTP verbs, meaningful status codes) with Level 3 hypermedia `links` on paginated collections.

---

## 1. Resource Naming & URI Rules

1. **Plural nouns for collections:** `/dishes`, `/restaurants`, `/orders`, `/uploads` (never `/dish` or `/getDishes`).
2. **Lowercase, hyphen-separated words:** `/order-lines`, `/delivery-zones` (never camelCase or underscores).
3. **No verbs in URIs:** The HTTP method is the verb (`DELETE /api/v1/dishes/42`, never `POST /api/v1/dishes/42/delete`).
4. **No file extensions or trailing slashes:** `/api/v1/dishes/42` (never `/dishes/42.json` or `/dishes/42/`).
5. **ASCII English URIs, UTF-8 payloads:** Path segments and generated slugs (`Str::slug()`) stay ASCII; dish names and messages support UTF-8 (including Amharic).

---

## 2. Full Capstone Resource Endpoint Table (Day 56 – Day 75)

| URI | Method | What it does | Success code | Failure codes | Authentication required |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `/api/v1/dishes` | `GET` | List paginated dishes (`area`, `max_price`, `fasting`, `q`, `sort`, `page`, `per_page`) | `200 OK` | `422` | Public (No) |
| `/api/v1/dishes` | `POST` | Create a new dish | `201 Created` + `Location` | `401`, `403`, `422` | Yes (Restaurant Owner / Admin) |
| `/api/v1/dishes/{dish}` | `GET` | Retrieve a single dish by numeric ID | `200 OK` | `404` | Public (No) |
| `/api/v1/dishes/{dish}` | `PATCH` | Partially update a dish (e.g., price or fasting flag) | `200 OK` | `401`, `403`, `404`, `422` | Yes (Restaurant Owner / Admin) |
| `/api/v1/dishes/{dish}` | `DELETE` | Soft-delete a dish | `204 No Content` | `401`, `403`, `404` | Yes (Restaurant Owner / Admin) |
| `/api/v1/restaurants` | `GET` | List paginated restaurants (`area`, `q`, `sort`, `include=dishes`, `page`, `per_page`) | `200 OK` | `422` | Public (No) |
| `/api/v1/restaurants` | `POST` | Create a new restaurant | `201 Created` + `Location` | `401`, `403`, `422` | Yes (Restaurant Owner / Admin) |
| `/api/v1/restaurants/{restaurant}` | `GET` | Retrieve a restaurant by `slug` (`include=dishes`) | `200 OK` | `404` | Public (No) |
| `/api/v1/restaurants/{restaurant}` | `PATCH` | Partially update a restaurant by `slug` | `200 OK` | `401`, `403`, `404`, `422` | Yes (Restaurant Owner / Admin) |
| `/api/v1/restaurants/{restaurant}` | `DELETE` | Delete a restaurant by `slug` | `204 No Content` | `401`, `403`, `404`, `409` | Yes (Admin) |
| `/api/v1/restaurants/{restaurant}/dishes` | `GET` | List paginated dishes belonging to a restaurant (`slug`) | `200 OK` | `404`, `422` | Public (No) |
| `/api/v1/restaurants/{restaurant}/dishes` | `POST` | Create a new dish under a specific restaurant (`slug`) | `201 Created` + `Location` | `401`, `403`, `404`, `422` | Yes (Restaurant Owner / Admin) |
| `/api/v1/orders` | `GET` | List paginated orders for the authenticated user or role | `200 OK` | `401`, `422` | Yes (Authenticated User) |
| `/api/v1/orders` | `POST` | Place a new stateless order with delivery area and line items | `201 Created` + `Location` | `401`, `409`, `422` | Yes (Customer) |
| `/api/v1/orders/{order}` | `GET` | Retrieve a single order with its lines and current status | `200 OK` | `401`, `403`, `404` | Yes (Order Owner / Restaurant / Driver) |
| `/api/v1/orders/{order}/lines` | `GET` | List line items belonging to an order | `200 OK` | `401`, `403`, `404` | Yes (Order Owner / Restaurant / Driver) |
| `/api/v1/orders/{order}/receipt` | `GET` | Retrieve the singleton receipt for a completed order | `200 OK` | `401`, `403`, `404`, `409` | Yes (Order Owner / Admin) |
| `/api/v1/orders/{order}/status` | `PATCH` | Transition order status (`confirmed`, `preparing`, `dispatched`, `delivered`, `cancelled`) | `200 OK` | `401`, `403`, `404`, `409`, `422` | Yes (Role-restricted per transition) |
| `/api/v1/orders/{order}/payments` | `POST` | Record a TeleBirr or cash payment attempt for an order | `201 Created` + `Location` | `401`, `403`, `404`, `409`, `422` | Yes (Customer / Driver) |
| `/api/v1/auth/register` | `POST` | Register a new user account and issue a bearer token | `201 Created` | `422` | Public (No) |
| `/api/v1/auth/login` | `POST` | Authenticate credentials and issue a stateless bearer token | `200 OK` | `401`, `422` | Public (No) |
| `/api/v1/auth/logout` | `POST` | Revoke the current bearer token | `204 No Content` | `401` | Yes (Authenticated User) |
| `/api/v1/me` | `GET` | Retrieve the authenticated user's singleton profile | `200 OK` | `401` | Yes (Authenticated User) |
| `/api/v1/me` | `PATCH` | Update the authenticated user's profile fields | `200 OK` | `401`, `422` | Yes (Authenticated User) |
| `/api/v1/uploads` | `POST` | Upload an image (e.g., dish photo) and return its stored URL/metadata | `201 Created` + `Location` | `401`, `403`, `422` | Yes (Restaurant Owner / Admin) |
| `/api/v1/uploads/{upload}` | `DELETE` | Remove an uploaded asset | `204 No Content` | `401`, `403`, `404` | Yes (Restaurant Owner / Admin) |

---

## 3. Non-CRUD Order State Transitions

Order status changes are modeled as a dedicated sub-resource (`PATCH /api/v1/orders/{order}/status`) backed by a single-action `OrderStatusController`:

- **Cancel an order before dispatch:** `PATCH /api/v1/orders/{order}/status` with body `{"status": "cancelled"}` (authorized only for the customer or assigned driver while status is `placed` or `confirmed`; returns `409 Conflict` once `dispatched` or `delivered`).
- **Mark an order delivered:** `PATCH /api/v1/orders/{order}/status` with body `{"status": "delivered"}` (authorized only for the assigned driver when status is `dispatched`; returns `409 Conflict` if already cancelled or not yet dispatched).
