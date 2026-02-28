# DividendFlow

A dividend portfolio tracker built with Vue 3 and PHP 8.4. Track holdings, visualize income, project growth with DRIP simulation, and manage your dividend strategy.

## URL

Not hosted online. The application runs locally via Docker (see [Getting Started](#getting-started)).

## Login Credentials

| Role  | Email                  | Password |
|-------|------------------------|----------|
| Admin | admin@dividendflow.nl  | secret   |
| User  | user@dividendflow.nl   | secret   |

---

## Getting Started

### Prerequisites

- Docker Desktop

### Setup

```bash
docker compose up --build -d
```

Wait about 30 seconds for all services to initialize, then open http://localhost:8000.

### Rebuild Database

```bash
docker compose down -v
docker compose up -d
```

---

## Required Functionality

DividendFlow is a realistic dividend investment portfolio tracker. Users can import their stock holdings (via CSV or manual entry), and the application calculates dividend income per month, projects long-term DRIP (Dividend Reinvestment Plan) growth, and helps identify optimal buying windows before ex-dividend dates.

This goes beyond what was discussed in the lectures by implementing:

- **Real-time stock data** fetched from Yahoo Finance's API, including price, dividend per share, yield, and ex-dividend dates (`api/services/YahooFinanceService.php`)
- **DRIP growth projection** with configurable monthly contributions, dividend growth rate, and price growth rate over up to 30 years (`api/services/PortfolioService.php:projectGrowth`)
- **Computed portfolio analytics**: sector allocation, weighted yield, monthly/daily income breakdowns, investment window detection (`api/services/PortfolioService.php`)
- **CSV import** supporting Trading 212 export format with automatic stock creation and price lookup (`api/controllers/HoldingController.php:import`)
- **Admin panel** with dashboard statistics, user management (search, filter, bulk actions, password reset), and stock management (edit, delete, refresh from Yahoo Finance) (`api/controllers/AdminController.php`, `api/controllers/UserController.php`)
- **Interactive income timeline** showing projected monthly dividend income from today to 20 years in the future with scrollable SVG chart (`frontend/src/components/dashboard/IncomeTimeline.vue`)

## CSS

The application is styled using **Tailwind CSS** (utility-first CSS framework) combined with **Heroicons** for icons.

- Consistent look & feel: all components use the same slate/blue color palette with consistent spacing, border radius, and typography
- **Responsive design**: every page adapts to smartphone, tablet, laptop, and desktop viewports using Tailwind's responsive prefixes (`sm:`, `md:`, `lg:`)
  - The sidebar collapses to icons on desktop and becomes a slide-out drawer on mobile (`frontend/src/components/layout/SideBar.vue`)
  - Tables use `overflow-x-auto` for horizontal scrolling on small screens
  - Grid layouts switch from multi-column to single-column (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`)
- **Dark mode** is fully supported with Tailwind's `dark:` variant, toggled via a composable (`frontend/src/composables/useTheme.js`) that persists the user's preference to localStorage

Key styling files:
- Tailwind config: `frontend/tailwind.config.js`
- Layout components: `frontend/src/components/layout/AppLayout.vue`, `SideBar.vue`, `NavBar.vue`

## Frontend Architecture

The frontend is a **Vue 3 Single Page Application** using the Composition API (`<script setup>`).

### Component Structure

```
frontend/src/
├── views/              8 page-level views (one per route)
├── components/
│   ├── admin/          AdminOverview, StockManagement, UserManagement
│   ├── calendar/       MonthlyOverview, UpcomingEvents
│   ├── dashboard/      SummaryCards, IncomeTimeline, SectorPieChart, TopEarners, BestBuyWindows
│   ├── import/         CsvImport, ManualAddForm
│   ├── layout/         AppLayout, SideBar, NavBar
│   ├── portfolio/      HoldingsTable, ColumnPicker
│   ├── projection/     ProjectionControls, GrowthChart, DividendGrowthChart, MilestonesTable
│   └── shared/         LoadingSpinner, ErrorAlert
├── stores/             Pinia state management
├── composables/        Reusable logic (useTheme, useSidebar)
├── router/             Vue Router configuration
└── api/                Axios instance with JWT interceptor
```

### Routing

Implemented in `frontend/src/router/index.js` using **vue-router** with:
- Route guards for authentication (`requiresAuth`), guest-only pages (`requiresGuest`), admin-only pages (`requiresAdmin`), and user-only pages (`requiresUser`)
- Nested routes for the admin panel (`/admin/overview`, `/admin/users`, `/admin/stocks`)
- Lazy-loaded route components via dynamic `import()`

### State Management

Implemented using **Pinia** stores:
- `frontend/src/stores/auth.js` — authentication state (token, user), login/logout/register actions
- `frontend/src/stores/portfolio.js` — holdings, summary, sectors, calendar data, CRUD actions
- `frontend/src/stores/stocks.js` — stock listing with search, sort, pagination, refresh
- `frontend/src/stores/projection.js` — DRIP projection parameters and computed data

## REST API

The backend provides a full RESTful API with correct HTTP methods and endpoint naming.

### Endpoints

| Method | Endpoint                      | Auth  | Description                    |
|--------|-------------------------------|-------|--------------------------------|
| POST   | /login                        | -     | Authenticate, returns JWT      |
| POST   | /register                     | -     | Create account, returns JWT    |
| GET    | /stocks                       | Auth  | List stocks (paginated)        |
| GET    | /stocks/{id}                  | Auth  | Get single stock               |
| GET    | /stocks/lookup/{ticker}       | Auth  | Lookup stock on Yahoo Finance  |
| POST   | /stocks                       | Auth  | Create stock (returns with id) |
| POST   | /stocks/refresh/{ticker}      | Auth  | Refresh stock from Yahoo       |
| PUT    | /stocks/{id}                  | Admin | Update stock                   |
| DELETE | /stocks/{id}                  | Admin | Delete stock                   |
| GET    | /holdings                     | Auth  | List user's holdings           |
| GET    | /holdings/{id}                | Auth  | Get single holding             |
| POST   | /holdings                     | Auth  | Create holding (returns with id) |
| PUT    | /holdings/{id}                | Auth  | Update holding                 |
| DELETE | /holdings/{id}                | Auth  | Delete holding                 |
| POST   | /holdings/{id}/sell           | Auth  | Sell shares from holding       |
| POST   | /holdings/import              | Auth  | Import holdings from CSV       |
| GET    | /transactions                 | Auth  | List user's transactions       |
| GET    | /portfolio/summary            | Auth  | Portfolio totals               |
| GET    | /portfolio/sectors            | Auth  | Sector allocation              |
| GET    | /portfolio/calendar           | Auth  | Monthly income + events        |
| GET    | /portfolio/projection         | Auth  | DRIP growth projection         |
| GET    | /admin/stats                  | Admin | Dashboard statistics           |
| GET    | /users                        | Admin | List users (paginated)         |
| PUT    | /users/{id}                   | Admin | Update user role               |
| DELETE | /users/{id}                   | Admin | Delete user                    |
| PUT    | /users/{id}/password          | Admin | Reset user password            |
| POST   | /users/bulk-role              | Admin | Bulk update user roles         |
| POST   | /users/bulk-delete            | Admin | Bulk delete users              |

### Filtering & Pagination

- **Stock listing** supports `search`, `sector`, `sort`, `direction`, `page`, and `limit` query parameters — see `api/controllers/StockController.php:getAll` and `api/repositories/StockRepository.php:getAll`
- **User listing** supports `search`, `role`, `page`, and `limit` query parameters — see `api/controllers/UserController.php:getAll` and `api/repositories/UserRepository.php:getAll`
- **Holdings** support `sort` and `direction` parameters — see `api/repositories/HoldingRepository.php:getAllForUser`

### Error Handling

All endpoints return JSON error responses with appropriate HTTP status codes:
- `400` for missing/invalid request body (`api/controllers/UserController.php:update`)
- `401` for missing/invalid JWT (`api/middleware/AuthMiddleware.php:requireAuth`)
- `403` for insufficient role (`api/middleware/AuthMiddleware.php:requireAdmin`)
- `404` for resources not found (`api/controllers/StockController.php:getOne`)
- `409` for constraint violations (`api/controllers/StockController.php:delete`)
- `422` for validation errors (`api/controllers/UserController.php:update`)

## Authentication

Authentication is implemented using **JWT (JSON Web Tokens)** with the `firebase/php-jwt` library.

### How it works

1. User logs in via `POST /login` — credentials verified against bcrypt hash, JWT generated with user id, email, and role (`api/controllers/AuthController.php`)
2. Token stored in localStorage and sent via `Authorization: Bearer <token>` header on every request (`frontend/src/api/axios.js`)
3. Backend middleware validates the token on protected endpoints (`api/middleware/AuthMiddleware.php:requireAuth`)

### Role Based Access Control (RBAC)

Two roles exist: **User** (role=0) and **Admin** (role=1).

- Role is stored in the `users` table and embedded in the JWT payload (`api/controllers/AuthController.php`)
- Backend enforcement: `AuthMiddleware::requireAdmin()` checks `$token->data->role === 1` and returns 403 if not — see `api/middleware/AuthMiddleware.php:48-60`
- Frontend enforcement: route guard in `frontend/src/router/index.js:63-83` checks `user.role` and redirects accordingly
- Admin users are redirected to `/admin/overview`, regular users cannot access `/admin/*` routes
- Admin users cannot modify or delete their own account (self-protection in `api/controllers/UserController.php:update` and `delete`)

## Backend Code Architecture

The backend follows the **MVC pattern** (without views, since the frontend is a separate SPA):

```
api/
├── controllers/        Request handling, validation, JSON responses
│   ├── Controller.php          Base controller with respond/error helpers
│   ├── AuthController.php      Login and register
│   ├── AdminController.php     Admin dashboard stats
│   ├── UserController.php      User CRUD + bulk operations
│   ├── StockController.php     Stock CRUD + Yahoo Finance refresh
│   ├── HoldingController.php   Holding CRUD + CSV import + sell
│   ├── PortfolioController.php Computed portfolio endpoints
│   └── TransactionController.php Transaction listing
├── services/           Business logic layer
│   ├── PortfolioService.php    Summary, sectors, calendar, projection
│   ├── StockService.php        Stock operations + refresh logic
│   ├── YahooFinanceService.php External API integration
│   └── ...
├── repositories/       Database access (PDO prepared statements)
│   ├── Repository.php          Base class with DB connection
│   ├── UserRepository.php      User queries
│   ├── StockRepository.php     Stock queries with search/filter
│   ├── HoldingRepository.php   Holding queries, user-scoped
│   └── TransactionRepository.php
├── models/             Typed data classes
│   ├── User.php, Stock.php, Holding.php, Transaction.php
├── middleware/
│   └── AuthMiddleware.php      JWT validation + role checking
└── public/
    └── index.php               Router entry point (bramus/router)
```

- **Routing**: `api/public/index.php` using `bramus/router`
- **Namespaces**: all classes use PSR-4 namespaces (`Controllers\`, `Services\`, `Repositories\`, `Models\`, `Middleware\`)
- **Autoloading**: Composer autoloader (`api/vendor/autoload.php`)

## Database

The SQL creation script is located at `sql/developmentdb.sql`. It creates all tables with foreign keys, indexes, and seed data (50 stocks, demo user holdings). The script is automatically executed when the Docker MariaDB container starts for the first time.
