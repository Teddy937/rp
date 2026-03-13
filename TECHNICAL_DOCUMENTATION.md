# KK Wholesalers — Technical Documentation

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Technology Stack](#2-technology-stack)
3. [Architecture](#3-architecture)
4. [Backend Structure](#4-backend-structure)
5. [Database Schema](#5-database-schema)
6. [Authentication & Security](#6-authentication--security)
7. [API Reference](#7-api-reference)
8. [Design Patterns](#8-design-patterns)
9. [Frontend Structure](#9-frontend-structure)
10. [Roles & Permissions](#10-roles--permissions)
11. [Error Handling](#11-error-handling)
12. [Setup & Installation](#12-setup--installation)
13. [Seeded Data](#13-seeded-data)

---

## 1. System Overview

KK Wholesalers is a multi-branch, multi-store inventory management system. It tracks stock movements (sales, transfers, adjustments, procurement) across 2 branches and 3 stores, with full traceability via an audit log and role-scoped access control.

**Key capabilities:**
- Real-time stock ledger per store per SKU
- Stock movement recording with observer-driven ledger updates
- Inter-store transfer workflow with approval/rejection
- Two-factor authentication (email OTP) with httpOnly session cookie
- Role-based access control via Spatie Laravel Permission
- Full audit trail on all mutations

---

## 2. Technology Stack

### Backend
| Concern | Technology |
|---|---|
| Framework | Laravel 11 |
| Auth | Laravel Sanctum (httpOnly cookie) |
| Permissions | Spatie Laravel Permission v6 |
| API Style | RESTful JSON, versioned under `/api/v1` |
| Database | MySQL / MariaDB |
| ORM | Eloquent |

### Frontend
| Concern | Technology |
|---|---|
| Framework | Vue 3 (Composition API, `<script setup>`) |
| State Management | Pinia (with `pinia-plugin-persistedstate`) |
| HTTP Client | Axios (`withCredentials: true`) |
| UI Components | BootstrapVue 3 + Bootstrap 5 |
| Pagination | `laravel-vue-pagination` |
| Routing | Vue Router 4 |

---

## 3. Architecture

The request lifecycle follows a strict layered architecture:

```
HTTP Request
    │
    ▼
Form Request (validation)
    │
    ▼
Controller  ──► ApiResponse trait (envelope)
    │
    ▼
Service Layer  (business logic, transactions)
    │
    ▼
Repository Layer  (Eloquent queries)
    │
    ▼
Model  ──► Observer (side effects)
    │
    ▼
Database
```

Each layer has a single responsibility. Controllers never touch the database directly — they delegate to Services or Repositories.

---

## 4. Backend Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/V1/
│   │       ├── Auth/
│   │       │   └── AuthController.php
│   │       ├── BranchController.php
│   │       ├── StoreController.php
│   │       ├── SkuController.php
│   │       ├── StockLedgerController.php
│   │       ├── StockMovementController.php
│   │       ├── UserController.php
│   │       └── AuditLogController.php
│   ├── Requests/V1/            # Form Requests per domain
│   └── Resources/V1/           # API Resources (transformers)
│
├── Models/
│   ├── User.php
│   ├── Branch.php
│   ├── Store.php
│   ├── Sku.php
│   ├── StockLedger.php         # protected $table = 'stock_ledger'
│   ├── StockMovement.php
│   └── AuditLog.php
│
├── Repositories/
│   ├── Contracts/              # Interfaces (one file per interface — PSR-4)
│   │   ├── BranchRepositoryInterface.php
│   │   ├── StoreRepositoryInterface.php
│   │   ├── SkuRepositoryInterface.php
│   │   ├── StockLedgerRepositoryInterface.php
│   │   └── StockMovementRepositoryInterface.php
│   ├── BaseRepository.php
│   ├── BranchRepository.php
│   ├── StoreRepository.php
│   ├── SkuRepository.php
│   ├── StockLedgerRepository.php
│   └── StockMovementRepository.php
│
├── Services/
│   ├── StockLedgerService.php
│   └── StockMovementService.php
│
├── Observers/
│   ├── StockMovementObserver.php
│   └── SkuObserver.php
│
├── Traits/
│   └── ApiResponse.php
│
├── Helpers/                    # Global helper functions
└── Support/
    └── AuthCache.php           # Cache key builder for auth state
```

---

## 5. Database Schema

### Tables

#### `branches`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| code | string unique | e.g. `BRA` |
| location | string nullable | |
| phone | string nullable | |
| is_active | boolean | default true |
| timestamps, soft_deletes | | |

#### `stores`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| branch_id | FK → branches | |
| name | string | |
| code | string unique | e.g. `STA1` |
| location | string nullable | |
| phone | string nullable | |
| is_active | boolean | default true |
| timestamps, soft_deletes | | |

#### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| branch_id | FK → branches nullable | |
| store_id | FK → stores nullable | |
| name, email, phone | string | email + phone unique |
| national_id | string unique nullable | |
| gender | enum(male,female,other) nullable | |
| date_of_birth | date nullable | |
| address | string nullable | |
| account_status | enum(active,inactive,suspended,pending) | default active |
| password | string hashed | |
| password_changed_at | timestamp nullable | |
| password_expires_at | timestamp nullable | |
| otp | string nullable | hashed, 10min TTL |
| otp_expires_at | timestamp nullable | |
| otp_attempts | tinyint | max 3 |
| login_token | string nullable | 2FA bridge token |
| login_token_expires_at | timestamp nullable | 10min TTL |
| last_login_at, last_login_ip | timestamp/string | |
| is_online | boolean | |
| failed_login_attempts | smallint | |
| locked_until | timestamp nullable | |
| timestamps, soft_deletes | | |

#### `skus`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| code | string unique nullable | |
| unit | string | e.g. kg, pcs |
| description | text nullable | |
| unit_cost | decimal(12,2) | buying price |
| unit_price | decimal(12,2) | selling price |
| reorder_level | integer | default 10 |
| is_active | boolean | |
| metadata | json nullable | |
| timestamps, soft_deletes | | |

#### `stock_ledger`
> Note: table name is `stock_ledger` (no S). Model sets `protected $table = 'stock_ledger'`.

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| store_id | FK → stores | |
| sku_id | FK → skus | |
| quantity | decimal(12,4) | current on-hand |
| timestamps | | |

Unique constraint: `(store_id, sku_id)` — one ledger row per SKU per store.

#### `stock_movements`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reference_no | string unique | auto-generated |
| type | enum(sale, transfer_out, transfer_in, adjustment_in, adjustment_out, procurement) | |
| status | enum(pending, completed, rejected) | default completed |
| sku_id | FK → skus | |
| from_store_id | FK → stores nullable | source for transfers/sales |
| to_store_id | FK → stores nullable | destination for transfers/procurement |
| branch_id | FK → branches nullable | |
| quantity | decimal(12,4) | |
| unit_cost | decimal(12,2) nullable | |
| unit_price | decimal(12,2) nullable | |
| notes | text nullable | |
| rejection_reason | text nullable | |
| created_by | FK → users | |
| approved_by | FK → users nullable | |
| approved_at | timestamp nullable | |
| timestamps | | |

#### `audit_logs`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users nullable | null = system action |
| action | string(50) | created, updated, deleted, approved, rejected, login, logout |
| model_type | string(100) | fully qualified class name |
| model_id | bigint unsigned | |
| old_values | json nullable | |
| new_values | json nullable | |
| ip_address | string(45) nullable | |
| user_agent | text nullable | |
| description | string nullable | |
| created_at | timestamp | UPDATED_AT is null — write-once |

---

## 6. Authentication & Security

### Two-Factor Login Flow

```
POST /api/v1/auth/login
  → validates email + password
  → issues login_token (hashed, 10min TTL) stored on user row
  → generates OTP (hashed), sends via notification
  → returns { login_token } to client

POST /api/v1/auth/verify-otp
  → validates login_token + otp
  → checks OTP attempts (max 3), expiry
  → clears login_token + otp fields
  → creates Sanctum token
  → sets token as httpOnly cookie 'vcf'
  → returns user + permissions
```

### Token Storage
Sanctum tokens are stored as **httpOnly cookies** (`vcf`) — never exposed to JavaScript. All API calls use `withCredentials: true` in Axios so the cookie is sent automatically.

### Session Heartbeat
`GET /api/v1/heartbeat` — called periodically by the frontend to keep the session alive. Returns cached user state (roles, permissions) from a 12-hour Redis/cache entry. Cache is keyed via `AuthCache::key($userId)`.

### Password Security
- Passwords are hashed with `Hash::make()` (bcrypt)
- `password_changed_at` is recorded on every change
- `failed_login_attempts` is tracked; account is locked via `locked_until` after repeated failures
- `password_expires_at` supports forced rotation policies

---

## 7. API Reference

All endpoints are prefixed `/api/v1`. Authenticated endpoints require a valid Sanctum session cookie.

### Auth (Public)
| Method | Endpoint | Description |
|---|---|---|
| POST | `/auth/login` | Step 1: credentials → login_token |
| POST | `/auth/verify-otp` | Step 2: OTP → session cookie |
| POST | `/auth/resend-otp` | Resend OTP |
| POST | `/auth/forgot-password` | Send password reset link |
| POST | `/auth/reset-password` | Reset via token |

### Auth (Authenticated)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/auth/me` | Current user + permissions |
| POST | `/auth/logout` | Destroy session |
| POST | `/auth/change-password` | Change own password |
| GET | `/heartbeat` | Keep-alive + cached user state |

### Branches
| Method | Endpoint | Description |
|---|---|---|
| GET | `/branches` | Paginated list (filterable) |
| POST | `/branches` | Create branch |
| GET | `/branches/{id}` | Show branch |
| PUT | `/branches/{id}` | Update branch |
| DELETE | `/branches/{id}` | Soft delete |

### Stores
| Method | Endpoint | Description |
|---|---|---|
| GET | `/stores` | Paginated list (filterable by branch) |
| POST | `/stores` | Create store |
| GET | `/stores/{id}` | Show store |
| PUT | `/stores/{id}` | Update store |
| DELETE | `/stores/{id}` | Soft delete |

### SKUs
| Method | Endpoint | Description |
|---|---|---|
| GET | `/skus` | Paginated list |
| POST | `/skus` | Create SKU |
| GET | `/skus/{id}` | Show SKU |
| PUT | `/skus/{id}` | Update SKU |
| DELETE | `/skus/{id}` | Soft delete |

### Stock Ledger
| Method | Endpoint | Description |
|---|---|---|
| GET | `/stock/stores/{store}/ledger` | All stock for a store |
| GET | `/stock/stores/{store}/skus/{sku}` | Single SKU stock in store |
| GET | `/stock/stores/{store}/low-stock` | SKUs at/below reorder level |

### Stock Movements
| Method | Endpoint | Description |
|---|---|---|
| GET | `/movements` | Paginated list (filterable) |
| GET | `/movements/pending-transfers` | All pending transfers |
| GET | `/movements/{id}` | Movement detail |
| POST | `/movements/sale` | Record sale |
| POST | `/movements/transfer` | Initiate transfer |
| POST | `/movements/transfer/{id}/approve` | Approve transfer |
| POST | `/movements/transfer/{id}/reject` | Reject transfer |
| POST | `/movements/adjustment` | Record adjustment |
| POST | `/movements/procurement` | Record procurement |

### Users
| Method | Endpoint | Description |
|---|---|---|
| GET | `/users` | Paginated list |
| POST | `/users` | Create user |
| GET | `/users/{id}` | Show user |
| PUT | `/users/{id}` | Update user |
| DELETE | `/users/{id}` | Soft delete |
| POST | `/users/{id}/reset-password` | Reset password |
| POST | `/users/{id}/status` | Toggle account status |
| GET | `/users/roles` | List available roles |

### Audit Logs
| Method | Endpoint | Description |
|---|---|---|
| GET | `/audit-logs` | Paginated list (filterable) |

### Standard Response Envelope
All responses follow this structure:
```json
{
  "status": "success | error",
  "code": 200,
  "message": "Human readable message",
  "data": { ... },
  "errors": null
}
```

Paginated responses include an additional `pagination` key:
```json
{
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

---

## 8. Design Patterns

### Repository Pattern
All database access goes through repository classes. Controllers never call Eloquent directly (except `UserController` which uses the model directly for simplicity given its inlined validation).

```
Interface (Contracts/)  ←  Repository  ←  Controller / Service
```

`BaseRepository` provides: `all()`, `find()`, `findOrFail()`, `create()`, `update()`, `delete()`, `paginate()`.

Domain repositories extend `BaseRepository` and add query-specific methods (e.g. `StockLedgerRepository::getLowStockItems()`).

> **PSR-4 Note:** Each interface lives in its own file under `Repositories/Contracts/`. A single `RepositoryInterfaces.php` file breaks autoloading — this was a known fix during development.

---

### Service Layer
Business logic that spans multiple models or requires transactions lives in Services.

`StockMovementService` handles:
- `recordSale()` — validates stock availability, creates movement
- `initiateTransfer()` — creates movement with `pending` status
- `approveTransfer()` — wraps in a DB transaction, updates status, triggers observer
- `rejectTransfer()` — updates status + stores rejection reason
- `recordAdjustment()` — in/out adjustment with mandatory notes
- `recordProcurement()` — adds incoming stock

`StockLedgerService` handles:
- `applyMovement()` — dispatches to `addStock()` or `deductStock()` based on movement type
- Uses `updateOrCreate` on `stock_ledger` to ensure one row per store/SKU pair

---

### Observer Pattern
`StockMovementObserver` listens to `created` and `updated` events on `StockMovement`.

When a movement reaches `status = completed`, it calls `StockLedgerService::applyMovement()` to update the ledger. This keeps the ledger in sync automatically without the controller needing to know about it.

```
StockMovement::create(...)
    → StockMovementObserver::created()
        → StockLedgerService::applyMovement()
            → stock_ledger row updated
```

---

### ApiResponse Trait
All controllers use the `ApiResponse` trait for consistent JSON envelopes.

```php
$this->success($data, 'Message');          // 200
$this->created($data, 'Message');          // 201
$this->paginated($resourceCollection);     // 200 + pagination meta
$this->error('Message', 422);             // 4xx
$this->forbidden('Message');               // 403
$this->notFound('Message');               // 404
$this->serverError('Message');            // 500
```

---

### Error Handling Convention
Every controller method wraps its body in `try { ... } catch (\Throwable $th)`. Validation exceptions and authorization exceptions bubble up naturally through Laravel's exception handler (they return 422 and 403 respectively). The `\Throwable` catch is a safety net for unexpected errors.

---

### scopeLowStock — Dynamic Table Fix
`StockLedger::scopeLowStock()` uses a dynamic table name to avoid an Eloquent query builder table name mismatch bug:

```php
public function scopeLowStock($query) {
    $table = $query->getModel()->getTable(); // resolves to 'stock_ledger'
    return $query->whereRaw(
        "`{$table}`.`quantity` <= (
            select `reorder_level` from `skus`
            where `skus`.`id` = `{$table}`.`sku_id`
            and `skus`.`deleted_at` is null
        )"
    );
}
```

---

## 9. Frontend Structure

```
src/
├── api/
│   ├── http.js                    # Axios instance (withCredentials: true)
│   ├── auth/authApi.js
│   ├── branches/branchesApi.js
│   ├── skus/skuApi.js
│   ├── stock/stockApi.js
│   ├── movements/movementsApi.js
│   └── users/usersApi.js          # users + audit logs
│
├── stores/                        # Pinia stores
│   ├── authStore.ts               # persisted — user, token, permissions
│   └── apiState.js                # loading, saving, error, success, message
│
├── views/
│   ├── auth/
│   │   ├── login.vue
│   │   └── login-otp.vue
│   ├── branches/
│   │   ├── BranchView.vue
│   │   ├── BranchDetailView.vue
│   │   ├── StoreView.vue
│   │   └── StoreDetailView.vue
│   ├── skus/
│   │   ├── SkuView.vue
│   │   └── SkuCreate.vue          # handles both create + edit
│   ├── stock-ledger/
│   │   ├── StoreStockView.vue
│   │   └── StockAlerts.vue
│   ├── movements/
│   │   ├── MovementListView.vue
│   │   ├── PendingTransfersView.vue
│   │   ├── RecordSale.vue
│   │   ├── RecordTransfer.vue
│   │   ├── RecordProcurement.vue
│   │   └── RecordAdjustment.vue
│   ├── users/
│   │   ├── UserListView.vue
│   │   └── UserCreate.vue         # handles both create + edit
│   └── audit/
│       └── AuditLogView.vue
│
└── layouts/
    └── DefaultLayout.vue
```

### State Management — apiState
Every view uses the shared `apiState` Pinia store to drive UI states:

```js
apiState.resetState()         // clear all flags before every operation
apiState.setLoading(true)     // show skeleton/spinner
apiState.setSaving(true)      // disable submit button
apiState.setError(true)       // show error alert
apiState.setSuccess(true)     // show success alert
apiState.setMessage('...')    // message text
```

`StatesComponent` in every card body reads these flags and renders the appropriate alert automatically.

### Pagination Convention
- Data refs are initialised as `ref({})` not `ref([])`
- Paginated data is accessed as `data.data` (the Laravel paginator structure)
- `Bootstrap5Pagination` is bound with `@pagination-change-page="fetchList"` where `fetchList(page = 1)` accepts the page number directly

### API Call Convention
```js
const { data } = await someApi.method(params);
```
All API service methods return `response.data` from Axios, so destructuring `{ data }` gives the Laravel response envelope. The actual payload is then `data.data`.

### Router Route Names
```
auth.sign-otp               branches.details            store.details
sku.view                    sku.create                  sku.edit
stock.lists                 stock.alerts
stock.movement.list         stock.movement.pending
stock.movement.record.sale  stock.movement.record.transfer
stock.movement.record.procurement  stock.movement.record.adjustment
users.list                  users.create                users.edit
audit.logs
```

---

## 10. Roles & Permissions

Roles and permissions are managed by **Spatie Laravel Permission** with guard `sanctum`.

### Roles
| Role | Scope |
|---|---|
| Administrator | Full system access |
| Branch Manager | Scoped to their assigned branch |
| Store Manager | Scoped to their assigned store |

### Permissions
```
Can manage branches       Can view branches
Can manage stores         Can view stores
Can manage skus           Can view skus
Can view stock ledger
Can record sale
Can request transfer      Can verify transfer      Can approve transfer
Can manage adjustments    Can manage procurement
Can manage users          Can view users
Can view audit logs       Can view reports
```

### Scope Enforcement
Controllers check the authenticated user's role and narrow queries accordingly:

```php
// StoreController
if ($user->isBranchManager()) {
    $filters['branch_id'] = $user->branch_id;
}

// StockMovementController
if ($user->isStoreManager()) {
    $filters['store_id'] = $user->store_id;
} elseif ($user->isBranchManager()) {
    $filters['branch_id'] = $user->branch_id;
}
```

---

## 11. Error Handling

### Backend
- `\Throwable` catch on every controller method returns `$this->error($th->getMessage())`
- Validation exceptions return 422 with field-level errors via Laravel's default handler
- `ModelNotFoundException` (from `findOrFail`) returns a 404 via Laravel's default handler
- `AuthorizationException` (from `$this->authorize()`) returns 403 via Laravel's default handler

### Frontend
- All API service methods wrap Axios calls in try/catch and throw `CustomAxiosError`
- Views catch errors, set `apiState.setError(true)` and `apiState.setMessage()`
- `StatesComponent` renders the error alert from those flags
- Form submissions disable the submit button via `apiState.saving` to prevent double-submits

---

## 12. Setup & Installation

### Backend

```bash
# 1. Install dependencies
composer install

# 2. Copy environment file
cp .env.example .env
php artisan key:generate

# 3. Configure .env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:3000
SESSION_DOMAIN=localhost
DB_DATABASE=kkwholesalers
DB_USERNAME=root
DB_PASSWORD=

# 4. Run migrations and seed
php artisan migrate
php artisan db:seed

# Optional: seed stock ledger with random dev data
php artisan db:seed --class=StockLedgerSeeder

# 5. Start server
php artisan serve --host=localhost --port=8000
```

### Frontend

```bash
# 1. Install dependencies
npm install

# 2. Configure .env
VITE_API_BASE_URL=http://localhost:8000/api

# 3. Start dev server
npm run dev
```

### Required Packages
```bash
# Backend
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan install:api   # installs Sanctum

# Frontend
npm install laravel-vue-pagination pinia pinia-plugin-persistedstate axios
```

---

## 13. Seeded Data

After running `php artisan db:seed`:

### Branches & Stores
| Branch | Code | Stores |
|---|---|---|
| Branch A | BRA | Store A1 (STA1) |
| Branch B | BRB | Store B1 (STB1), Store B2 (STB2) |

### Users
| Email | Role | Assignment |
|---|---|---|
| admin@kkwholesalers.co.ke | Administrator | — |
| alice@kkwholesalers.co.ke | Branch Manager | Branch A |
| brian@kkwholesalers.co.ke | Branch Manager | Branch B |
| carol@kkwholesalers.co.ke | Store Manager | Store A1 |
| david@kkwholesalers.co.ke | Store Manager | Store B1 |
| eve@kkwholesalers.co.ke | Store Manager | Store B2 |

All passwords: `password`

### SKUs
10 SKUs seeded with unit costs, prices, and reorder levels.

### Stock Ledger
Run `php artisan db:seed --class=StockLedgerSeeder` to seed random quantities (0–200) for every SKU across all stores. Used for development and testing only.

---

*KK Wholesalers Inventory System — Technical Documentation v1.0*