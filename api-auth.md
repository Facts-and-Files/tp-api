# API Authentication

This API uses a custom token-based auth mechanism. Tokens are stored in `api_clients`
and passed as a Bearer token on every request.

```
Authorization: Bearer <token>
```

Tokens are generated via Artisan and never stored in plain text — keep them safe.

---

## Generating a Token

```bash
php artisan auth:generate-token [options]
```

| Option | Description |
|---|---|
| `--rw` | Full read/write access (default) |
| `--ro` | Read-only — 403 on all write requests |
| `--permissions=` | Granular abilities, repeatable |
| `--description=` | Label stored with the token |

```bash
# Full access
php artisan auth:generate-token --rw --description="Internal dashboard"

# Read-only
php artisan auth:generate-token --ro --description="Reporting pipeline"

# Read everywhere, write only on scores and campaigns
php artisan auth:generate-token \
    --permissions=read \
    --permissions=scores:write \
    --permissions=campaigns:write \
    --description="Score ingestion worker"

# Fully scoped to a single resource
php artisan auth:generate-token \
    --permissions=persons:read \
    --permissions=persons:write \
    --description="Persons sync service"
```

---

## Permission Values

| Value | Meaning |
|---|---|
| `*` | All methods on all routes |
| `read` | GET on all routes |
| `write` | POST/PUT/PATCH/DELETE on all routes |
| `{resource}:read` | GET on `/{resource}` only |
| `{resource}:write` | POST/PUT/PATCH/DELETE on `/{resource}` only |

Evaluation order per request:

1. No/invalid token → **401**
2. Token has `*` → **pass**
3. Route requires a named ability → check for it
4. Write request → require `write` or `{resource}:write`
5. Read request → require `read` or `{resource}:read`
6. No match → **403**

---

## Restricting a Route

Pass a named ability as a middleware parameter in `routes/api.php`:

```php
// Single route
Route::post('/import', [ImportController::class, 'import'])
    ->middleware('api.permission:import:write');

// Group of routes
Route::middleware('api.permission:reports:read')->group(function () {
    Route::get('/users/statistics', [UserStatsController::class, 'index']);
    Route::get('/statistics/alltime', [StatisticsController::class, 'alltimeIndex']);
});
```

A `*` token always passes route-level ability checks.

---

## Database

Permissions are stored in a `text` column (MySQL does not allow defaults on `json` columns).

```
api_clients
├── id
├── api_token       — sha256 hash of the plain-text token
├── description     — nullable, human-readable label
└── permissions     — text, JSON array e.g. ["read", "scores:write"]
```
