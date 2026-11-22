# Addis Eats API Error Catalogue (`ERRORS.md`)

Every error returned by `/api/v1` uses a single JSON envelope emitted by `bootstrap/app.php` via `App\Http\Responses\ApiResponse::error()`:

    {
      "error": {
        "code": "validation_failed",
        "message": "The given data was invalid.",
        "meta": {
          "price": [
            "The price must be a positive amount of at least 100 santim (1.00 ETB)."
          ]
        }
      }
    }

- `error.code` is a stable, `snake_case` machine identifier that never changes across locales.
- `error.message` is a safe, human-readable summary that never exposes raw SQL, stack traces, or file paths.
- `error.meta` contains field validation errors (`422`), transition states (`409`), delivery zone context (`422`), a support log reference (`500`), or `[]` when no extra metadata applies.

## Error Code Catalogue

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
