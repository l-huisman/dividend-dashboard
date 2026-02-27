# DividendFlow

A full-stack dividend portfolio tracker built with Vue 3 and PHP 8.4. Track holdings, visualize income, project growth with DRIP simulation, and manage your dividend strategy.

## Tech Stack

- **Frontend:** Vue 3 (Composition API), Pinia, vue-router, Tailwind CSS, Chart.js
- **Backend:** PHP 8.4 (strict types), bramus/router, firebase/php-jwt
- **Database:** MariaDB
- **Infrastructure:** Docker Compose, nginx (reverse proxy), Vite (dev server)
- **Code Quality:** Laravel Pint (PSR-12), PHPStan level 6

## Getting Started

### Prerequisites

- Docker Desktop

### Setup

```bash
docker compose up --build -d
```

Wait about 30 seconds for all services to initialize, then open:

| Service      | URL                        |
|--------------|----------------------------|
| Application  | http://localhost:8000      |
| phpMyAdmin   | http://localhost:8080      |

### Credentials

| Role  | Email                       | Password |
|-------|-----------------------------|----------|
| Admin | admin@dividendflow.nl       | secret   |
| User  | user@dividendflow.nl        | secret   |

## Features

### User Features

- **Dashboard** - Summary cards, sector allocation charts, monthly income timeline, top earners, best buy windows
- **Portfolio** - Sortable holdings table with configurable columns, search, gain/loss tracking
- **Projection** - DRIP growth simulation with configurable parameters (monthly contribution, years, dividend/price growth rates), portfolio growth chart, dividend growth chart, income milestones
- **Calendar** - Monthly dividend breakdown, upcoming ex-dividend and payment dates, investment window alerts
- **Import** - CSV file import (Trading 212 format) with drag-and-drop, manual holding entry
- **Dark Mode** - System preference detection with manual toggle, persisted to localStorage

### Admin Features

- **User Management** - View all users, toggle roles, delete accounts

## Architecture

```
frontend/           Vue 3 SPA (Composition API, Pinia stores)
api/                PHP 8.4 REST API
  controllers/      Request handling, validation, response
  services/         Business logic, computation, external APIs
  repositories/     Database queries (PDO)
  models/           Data models with typed properties
  middleware/       JWT authentication
sql/                Database schema and seed data
```

### API Endpoints

| Method | Endpoint                  | Auth    | Description                |
|--------|---------------------------|---------|----------------------------|
| POST   | /api/login                | Public  | Authenticate user          |
| POST   | /api/register             | Public  | Create account             |
| GET    | /api/stocks               | User    | List stocks (paginated)    |
| GET    | /api/stocks/{id}          | User    | Get stock details          |
| POST   | /api/stocks/refresh/{t}   | User    | Refresh from Yahoo Finance |
| GET    | /api/holdings             | User    | List user holdings         |
| POST   | /api/holdings             | User    | Create holding             |
| PUT    | /api/holdings/{id}        | User    | Update holding             |
| DELETE | /api/holdings/{id}        | User    | Delete holding             |
| POST   | /api/holdings/import      | User    | Import CSV                 |
| GET    | /api/portfolio/summary    | User    | Portfolio totals           |
| GET    | /api/portfolio/sectors    | User    | Sector allocation          |
| GET    | /api/portfolio/calendar   | User    | Monthly income, events     |
| GET    | /api/portfolio/projection | User    | DRIP growth projection     |
| GET    | /api/users                | Admin   | List all users             |
| PUT    | /api/users/{id}           | Admin   | Update user role           |
| DELETE | /api/users/{id}           | Admin   | Delete user                |

## Development

### Code Quality

```bash
# Format PHP code (PSR-12)
docker exec -i $(docker ps -q -f name=php) /app/vendor/bin/pint

# Static analysis (level 6)
docker exec -i $(docker ps -q -f name=php) /app/vendor/bin/phpstan analyse --level 6 /app/controllers /app/services /app/repositories /app/models /app/middleware
```

### Rebuild Database

To reset the database with fresh seed data:

```bash
docker compose down -v
docker compose up -d
```

## Seed Data

The database ships with 50 real stocks across 10 sectors, complete with dividend data, payment schedules, and ex-dividend dates. The demo user account has holdings in all 50 stocks for testing.
