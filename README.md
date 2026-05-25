# 🏷️ Discount Pro

> **A full-featured Discount & Coupon Management Platform built with Laravel 12 + MongoDB**

Discount Pro is a production-ready web application that gives e-commerce businesses a powerful control panel to create, manage, and analyse discount codes and promotional campaigns — while simultaneously offering customers a clean storefront to browse live deals and apply coupons at checkout.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Tech Stack](#-tech-stack)
- [Architecture](#-architecture)
- [Database Design](#-database-design)
- [User Roles & Access Control](#-user-roles--access-control)
- [Application Modules](#-application-modules)
  - [Customer Side](#customer-side)
  - [Admin Panel](#admin-panel)
  - [Analytics](#analytics)
- [Discount Types](#-discount-types)
- [Coupon Validation Engine](#-coupon-validation-engine)
- [Project Structure](#-project-structure)
- [Installation & Setup](#-installation--setup)
- [Seeded Demo Data](#-seeded-demo-data)
- [Configuration](#-configuration)
- [Running the Application](#-running-the-application)
- [Testing](#-testing)
- [Security Highlights](#-security-highlights)

---

## 🌟 Overview

Discount Pro solves a common e-commerce problem: managing discount codes gets messy fast. This platform centralises everything — admins and store managers can create rich, rule-based discount codes and promotional banners, while customers can browse active deals and validate or apply coupon codes in real time.

The app is split into two clear experiences:

| Experience | Audience | Entry Point |
|---|---|---|
| **Customer Portal** | Shoppers | `/dashboard`, `/deals`, `/coupon` |
| **Admin Panel** | Store Managers & Super Admins | `/admin/dashboard` |

---

## ✨ Key Features

### For Customers
- 🔐 Secure authentication (register, login, password reset) via Laravel Breeze
- 🏷️ Browse all active deals and promotions filtered by type or search query
- ✅ Real-time coupon code validation (AJAX — no page refresh needed)
- 💰 Coupon application with instant savings summary
- 📜 Personal usage history — view all coupons you've previously applied
- 👤 Profile management (update name, email, password, delete account)

### For Admins & Store Managers
- 📊 Admin dashboard with live KPIs: active discounts, total revenue saved, daily usage count, expiring-soon alerts
- 🎫 Full CRUD for discount codes with advanced options
- 🔄 One-click discount toggle (activate/pause) without leaving the list view
- 📋 Duplicate any existing discount as a starting point for a new one
- 🎉 Full CRUD for promotional banners linked to discounts
- 📈 Analytics dashboard with time-range filters (7 / 30 / 90 days)
- 📤 CSV export of usage data for any time period
- 🔍 Per-discount detail view with 14-day usage sparkline chart

---

## 🛠️ Tech Stack

### Backend
| Technology | Version | Purpose |
|---|---|---|
| **PHP** | `^8.2` | Server-side language |
| **Laravel** | `^12.0` | MVC web framework |
| **mongodb/laravel-mongodb** | `^5.7` | MongoDB Eloquent ORM driver |
| **Laravel Breeze** | `^2.4` | Auth scaffolding (login, register, reset) |
| **Laravel Tinker** | `^2.10.1` | REPL for development |

### Database
| Technology | Purpose |
|---|---|
| **MongoDB** | Primary NoSQL database for all app data (users, discounts, promotions, usages, analytics) |
| **SQLite** | Fallback / local development (default in `.env.example`) |

### Frontend
| Technology | Version | Purpose |
|---|---|---|
| **Blade** | Laravel built-in | Server-rendered templating engine |
| **Tailwind CSS** | `^3.1.0` | Utility-first CSS framework |
| **Alpine.js** | `^3.4.2` | Lightweight reactivity (modals, toggles, AJAX interactions) |
| **@alpinejs/collapse** | `^3.15.12` | Collapse animation plugin for Alpine |
| **@alpinejs/focus** | `^3.15.12` | Focus management plugin for Alpine |
| **Axios** | `^1.11.0` | HTTP client for AJAX requests |
| **Vite** | `^7.0.7` | Frontend asset bundler (HMR in dev) |

### Dev & Tooling
| Tool | Purpose |
|---|---|
| **Laravel Pail** | Real-time log viewer in terminal |
| **Laravel Sail** | Docker environment support |
| **Laravel Pint** | PHP code style fixer |
| **PHPUnit** | `^11.5.50` — Testing framework |
| **Concurrently** | Runs all dev processes (server, queue, logs, vite) simultaneously |

---

## 🏗️ Architecture

Discount Pro follows strict **MVC (Model-View-Controller)** architecture with an additional **Service Layer** for complex business logic.

```
HTTP Request
     │
     ▼
  Routes (routes/web.php)
     │
     ├─── Middleware (Auth, ManagerMiddleware)
     │
     ▼
  Controllers
     │
     ├─── Admin\DashboardController
     ├─── Admin\DiscountController
     ├─── Admin\PromotionController
     ├─── Admin\AnalyticsController
     ├─── CouponController  ──────► CouponService (business logic)
     ├─── DealsController
     └─── ProfileController
     │
     ▼
  Models (MongoDB Eloquent)
     │
     ├─── User
     ├─── Discount
     ├─── Promotion
     ├─── DiscountUsage
     ├─── Product
     └─── AnalyticsSnapshot
     │
     ▼
  Views (Blade Templates)
     │
     ├─── layouts/app.blade.php        (Customer layout)
     ├─── layouts/admin.blade.php      (Admin layout)
     ├─── layouts/guest.blade.php      (Auth layout)
     └─── Component views per module
```

### Service Layer
The `CouponService` class (`app/Services/CouponService.php`) is the single source of truth for all coupon validation and application logic. It's injected into `CouponController` via Laravel's dependency injection container. This prevents duplication and makes the validation rules easily testable.

---

## 🗄️ Database Design

All collections live in **MongoDB**. The migrations use the `mongodb/laravel-mongodb` schema builder.

### Collections

#### `users`
| Field | Type | Description |
|---|---|---|
| `name` | string | Display name |
| `email` | string | Unique login email |
| `password` | hashed | Bcrypt-hashed password |
| `role` | string | `super_admin`, `store_manager`, or `customer` |
| `email_verified_at` | datetime | Email verification timestamp |

> ⚠️ `role` is **NOT fillable** — privilege escalation prevention. Only server-side `assignRole()` can change it.

#### `discounts`
| Field | Type | Description |
|---|---|---|
| `title` | string | Human-readable name |
| `code` | string (unique) | Coupon code (stored/validated uppercase) |
| `description` | string | Marketing description |
| `type` | enum | `percentage`, `fixed`, `bogo`, `free_shipping`, `tiered` |
| `value` | float | Discount value (% or fixed amount) |
| `tiered_rules` | array | Rules for tiered discounts `[{min, discount_pct}]` |
| `min_order_value` | float | Minimum cart value required |
| `max_uses` | int | Global usage cap (0 = unlimited) |
| `uses_per_user` | int | Per-user cap |
| `used_count` | int | Running counter of total redemptions |
| `applicable_to` | enum | `all`, `category`, `product` |
| `target_ids` | array | Category names or product IDs to restrict to |
| `target_label` | string | Human-readable target description |
| `start_date` | datetime | Activation date (null = immediate) |
| `end_date` | datetime | Expiry date (null = no expiry) |
| `is_active` | boolean | Manual on/off switch |
| `created_by` | string | ID of admin who created it |

**Indexes:** `code` (unique), `is_active`, `type`, `start_date`, `end_date`, `created_by`

#### `promotions`
| Field | Type | Description |
|---|---|---|
| `name` | string | Promotion name |
| `description` | string | Description text |
| `type` | enum | `flash_sale`, `seasonal`, `loyalty`, `referral` |
| `discount_id` | ObjectId | Linked discount code |
| `banner_color` | string | Hex/CSS color for the banner |
| `target_segment` | enum | `all`, `new_users`, `returning`, `high_value`, `inactive` |
| `start_at` | datetime | Campaign start |
| `end_at` | datetime | Campaign end |
| `is_active` | boolean | Manual toggle |
| `view_count` | int | Impression counter (auto-incremented on page view) |

**Indexes:** `is_active`, `type`, `discount_id`, `start_at`, `end_at`

#### `discount_usages`
| Field | Type | Description |
|---|---|---|
| `discount_id` | string | Reference to the discount |
| `user_id` | string | Reference to the user who applied it |
| `order_id` | UUID | Auto-generated unique order reference |
| `original_amount` | float | Cart total before discount |
| `discount_applied` | float | Amount saved |
| `final_amount` | float | Amount paid after discount |
| `used_at` | datetime | Timestamp of redemption |

**Indexes:** `discount_id`, `user_id`, `used_at`, compound `[discount_id, user_id]`

#### `products`
| Field | Type | Description |
|---|---|---|
| `name` | string | Product name |
| `category` | string | One of 6 predefined categories |
| `price` | float | Product price |
| `description` | string | Description |
| `image_url` | string | Image reference |
| `tags` | array | Searchable tags |
| `stock` | int | Stock quantity |
| `is_active` | boolean | Visibility flag |

**Categories:** Electronics, Clothing, Home & Kitchen, Books, Sports & Fitness, Beauty & Personal Care

#### `analytics_snapshots`
| Field | Type | Description |
|---|---|---|
| `discount_id` | string | The discount being tracked |
| `date` | datetime | Snapshot date |
| `total_uses` | int | Uses on this date |
| `revenue_saved` | float | Revenue saved on this date |
| `orders_count` | int | Orders using this discount |
| `conversion_rate` | float | Conversion rate percentage |

---

## 👥 User Roles & Access Control

The application implements a **3-tier role system**:

| Role | Constant | Email (seeded) | Access |
|---|---|---|---|
| **Super Admin** | `super_admin` | `admin@discountpro.com` | Full access — everything |
| **Store Manager** | `store_manager` | `manager@discountpro.com` | Admin panel (discounts, promotions, analytics) |
| **Customer** | `customer` | `customer@discountpro.com` | Customer portal only |

### Middleware Protection
- **`auth` middleware** — All routes require login
- **`manager` middleware** (`ManagerMiddleware`) — Admin routes additionally require `isManager()` which returns `true` for both `super_admin` and `store_manager` roles
- **Smart redirect** — Logging in as a manager automatically redirects to `/admin/dashboard` instead of the customer dashboard

### Privilege Escalation Prevention
The `role` field on the `User` model is **intentionally excluded from `$fillable`**. The only way to assign a role is via the server-side `$user->assignRole()` method, which validates the role against a whitelist before calling `forceFill()`.

---

## 📦 Application Modules

### Customer Side

#### Landing / Home (`/`)
- Unauthenticated visitors are redirected to the login page.

#### Dashboard (`/dashboard`)
- Authenticated customers see their personal dashboard.
- Managers/Admins are automatically bounced to `/admin/dashboard`.

#### Deals Page (`/deals`)
**Controller:** `DealsController`
- Lists all currently **active** discount codes (past start date, before end date).
- Displays active promotional banners with their linked discounts.
- Supports filtering by discount type and free-text search (title or code).
- Auto-increments `view_count` on all promotions rendered (bulk increment — single query, not N writes).

#### Coupon Tester (`/coupon`)
**Controller:** `CouponController`, **Service:** `CouponService`

Two-step flow:
1. **Validate** (`POST /coupon/validate`) — AJAX endpoint, returns validation result + savings preview without committing anything. Rate-limited to **30 requests/minute**.
2. **Apply** (`POST /coupon/apply`) — Commits the coupon, creates a `DiscountUsage` record, and increments `used_count`. Rate-limited to **5 requests/minute** (race-condition protection included).

Shows the user's last 8 coupon usage history.

#### Profile (`/profile`)
- Edit name/email/password
- Delete account

---

### Admin Panel

All admin routes live under the `/admin` prefix and are protected by `['auth', 'manager']` middleware.

#### Admin Dashboard (`/admin/dashboard`)
**Controller:** `Admin\DashboardController`

Live KPI cards:
- Active discounts count
- Total discounts count
- Total registered users
- Active promotions count
- Total revenue saved (all-time)
- Total coupon uses (all-time)
- Coupon uses today

Also shows:
- **Recent 5 usages** — order ID, coupon code, user name, savings, timestamp (loaded in 2 bulk queries — no N+1)
- **Expiring soon** — discounts expiring within the next 7 days
- **Top 5 discounts** — by all-time usage count

#### Discount Management (`/admin/discounts`)
**Controller:** `Admin\DiscountController`

Full RESTful resource plus two extra actions:

| Method | Route | Action |
|---|---|---|
| GET | `/admin/discounts` | List with search, type filter, status filter, sort |
| GET | `/admin/discounts/create` | Create form |
| POST | `/admin/discounts` | Store new discount |
| GET | `/admin/discounts/{id}` | Detailed view with 14-day sparkline |
| GET | `/admin/discounts/{id}/edit` | Edit form |
| PATCH | `/admin/discounts/{id}` | Update |
| DELETE | `/admin/discounts/{id}` | Delete |
| PATCH | `/admin/discounts/{id}/toggle` | **Toggle active/paused (AJAX)** |
| POST | `/admin/discounts/{id}/duplicate` | **Duplicate as draft** |

**Status filter tabs:** All · Active · Expired · Scheduled · Paused

**Sort options:** Created At · Usage Count · End Date · Title

#### Promotion Management (`/admin/promotions`)
**Controller:** `Admin\PromotionController`

Full RESTful resource plus toggle:

| Method | Route | Action |
|---|---|---|
| GET | `/admin/promotions` | List |
| GET | `/admin/promotions/create` | Create form |
| POST | `/admin/promotions` | Store |
| GET | `/admin/promotions/{id}` | Detail view |
| GET | `/admin/promotions/{id}/edit` | Edit form |
| PATCH | `/admin/promotions/{id}` | Update |
| DELETE | `/admin/promotions/{id}` | Delete |
| PATCH | `/admin/promotions/{id}/toggle` | **Toggle (AJAX)** |

Each promotion is linked to a discount code and targets a specific user segment.

---

### Analytics

**Controller:** `Admin\AnalyticsController`

#### Analytics Dashboard (`/admin/analytics`)
Time-range filter: **7 days / 30 days / 90 days**

Metrics shown:
- **All-time overview:** total revenue saved, total uses, total/active discounts, unique users, average order value
- **Period stats:** savings in period, uses in period, average savings per use
- **Daily chart data:** uses + savings per day (for the selected period)
- **Type breakdown:** usage and savings broken down by discount type (percentage, fixed, BOGO, etc.)
- **Top 10 discounts** by all-time usage count with per-discount revenue saved
- **Recent 20 usages** feed — code, type, amounts

All data is loaded efficiently: period usages are fetched in **one query** and grouped in PHP to avoid multiple round-trips.

#### CSV Export (`/admin/analytics/export?days=30`)
Downloads a CSV file with columns:
`Date`, `Order ID`, `Coupon Code`, `Type`, `Original Amount (₹)`, `Discount Applied (₹)`, `Final Amount (₹)`

---

## 🎫 Discount Types

| Type | Constant | How It Works |
|---|---|---|
| **Percentage** | `TYPE_PERCENTAGE` | `orderTotal × (value / 100)` |
| **Fixed Amount** | `TYPE_FIXED` | Flat deduction, capped at order total |
| **BOGO** | `TYPE_BOGO` | Buy-one-get-one — saves 50% of order total |
| **Free Shipping** | `TYPE_FREE_SHIPPING` | Waives shipping fee (₹99 constant) |
| **Tiered** | `TYPE_TIERED` | Slab-based — highest matching tier wins |

### Tiered Discount Example
```
SLAB2025:
  Spend ₹500+  → 5% off
  Spend ₹1000+ → 10% off
  Spend ₹2000+ → 15% off
```
Rules are sorted descending by `min` so the highest qualifying tier is always matched first.

### Applicability Scoping
Each discount can be scoped to:
- **All products** — applies unconditionally
- **By category** — e.g., `Electronics`, `Books`, `Clothing`
- **By product** — specific product IDs

When the API receives cart items, the `CouponService` checks if at least one item in the cart matches the discount's target before proceeding.

---

## ⚙️ Coupon Validation Engine

The `CouponService` (`app/Services/CouponService.php`) performs validation in this exact order:

```
1. Code lookup          → Does this code exist?
2. Active check         → Is is_active = true?
3. Start date check     → Has the discount started yet?
4. End date check       → Has the discount expired?
5. Global cap check     → Has max_uses been reached?
6. Min order check      → Does the order total meet min_order_value?
7. Per-user cap check   → Has this user exhausted uses_per_user?
8. Cart targeting check → Does the cart contain the required items? (optional)
9. Calculate savings    → Run type-specific savings formula
```

The `apply()` method **re-validates** before committing to prevent race conditions where a discount was valid at `validate()` time but expired by `apply()` time.

---

## 📁 Project Structure

```
discount-pro/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AnalyticsController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DiscountController.php
│   │   │   │   └── PromotionController.php
│   │   │   ├── CouponController.php
│   │   │   ├── DealsController.php
│   │   │   └── ProfileController.php
│   │   ├── Middleware/
│   │   │   └── ManagerMiddleware.php
│   │   └── Requests/
│   │       └── Admin/
│   │           └── DiscountRequest.php (form validation)
│   ├── Models/
│   │   ├── AnalyticsSnapshot.php
│   │   ├── Discount.php
│   │   ├── DiscountUsage.php
│   │   ├── Product.php
│   │   ├── Promotion.php
│   │   └── User.php
│   ├── Services/
│   │   └── CouponService.php
│   └── Providers/
├── database/
│   ├── migrations/           # MongoDB collection + index definitions
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolesAndAdminSeeder.php
│       ├── DiscountSeeder.php       # 12 sample discount codes
│       ├── PromotionSeeder.php
│       ├── ProductSeeder.php
│       ├── DiscountUsageSeeder.php
│       └── AnalyticsSnapshotSeeder.php
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── discounts/           # index, create, edit, show, _form
│       │   ├── promotions/          # index, create, edit, show, _form
│       │   └── analytics/           # index
│       ├── auth/                    # Breeze auth views
│       ├── layouts/
│       │   ├── admin.blade.php
│       │   ├── app.blade.php
│       │   ├── guest.blade.php
│       │   └── navigation.blade.php
│       ├── coupon/                  # try.blade.php
│       ├── profile/
│       ├── dashboard.blade.php
│       ├── deals.blade.php
│       └── welcome.blade.php
├── routes/
│   ├── auth.php                     # Breeze auth routes
│   └── web.php                      # All app routes
├── composer.json
├── package.json
├── tailwind.config.js               # Custom brand colours + animations
└── vite.config.js
```

---

## 🚀 Installation & Setup

### Prerequisites

- **PHP** `^8.2` with extensions: `mongodb`, `pdo`, `openssl`, `mbstring`, `tokenizer`, `xml`
- **Composer** `^2.x`
- **MongoDB** `^6.0` (running locally or via Atlas)
- **Node.js** `^18+` and **npm**

### Quick Setup (using the built-in script)

```bash
# 1. Clone the repository
git clone <repo-url> discount-pro
cd discount-pro

# 2. Run the all-in-one setup script
composer run setup
```

This single command:
1. Installs PHP dependencies (`composer install`)
2. Copies `.env.example` → `.env` (if not already present)
3. Generates the `APP_KEY`
4. Seeds the database with sample data
5. Installs Node dependencies (`npm install`)
6. Builds frontend assets (`npm run build`)

### Manual Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 3. Configure your .env (see Configuration section below)

# 4. Run migrations
php artisan migrate

# 5. Seed sample data
php artisan db:seed

# 6. Install and build frontend assets
npm install
npm run build
```

---

## 🌱 Seeded Demo Data

Running `php artisan db:seed` populates:

### Users (via `RolesAndAdminSeeder`)
| Email | Role | Password |
|---|---|---|
| `admin@discountpro.com` | Super Admin | `password` |
| `manager@discountpro.com` | Store Manager | `password` |
| `customer@discountpro.com` | Customer | `password` |

### Discounts (via `DiscountSeeder`) — 12 codes
| Code | Type | Value | Scope |
|---|---|---|---|
| `SUMMER25` | Percentage | 25% off | All products |
| `FLAT200` | Fixed | ₹200 off | All (min ₹1000) |
| `WELCOME10` | Percentage | 10% off | All (new users) |
| `ELEC15` | Percentage | 15% off | Electronics only |
| `BOGO_TS` | BOGO | 50% off | Clothing only |
| `SHIPFREE` | Free Shipping | ₹99 off | All (min ₹299) |
| `LOYAL20` | Percentage | 20% off | All products |
| `SLAB2025` | Tiered | 5/10/15% | All products |
| `READ15` | Percentage | 15% off | Books only |
| `WKND30` | Percentage | 30% off | All (scheduled) |
| `FIT10` | Percentage | 10% off | Sports & Fitness |
| `CLEAR50` | Percentage | 50% off | All (expired/inactive) |

### Also seeded:
- **Products** — sample product catalog across all 6 categories
- **Promotions** — flash sale & seasonal banners
- **Discount Usages** — realistic historical usage data
- **Analytics Snapshots** — pre-computed daily snapshot data

---

## ⚙️ Configuration

### `.env` Key Settings

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Default: SQLite (works out of the box for development)
DB_CONNECTION=sqlite

# ── MongoDB (required for full functionality) ──────────────────────────
DB_CONNECTION=mongodb
MONGODB_URI=mongodb://127.0.0.1:27017
MONGODB_DATABASE=discount_pro

# Sessions, cache, and queue all use the database driver by default
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

> **Note:** The default `.env.example` uses SQLite for the system tables (sessions, cache, jobs) but the MongoDB Laravel driver is used for all app models directly. Configure `MONGODB_URI` and `MONGODB_DATABASE` for full MongoDB connectivity.

---

## ▶️ Running the Application

### Development (all-in-one)

```bash
composer run dev
```

This concurrently starts:
- 🟦 **PHP dev server** — `php artisan serve` → `http://localhost:8000`
- 🟪 **Queue worker** — `php artisan queue:listen`
- 🩷 **Log viewer** — `php artisan pail`
- 🟧 **Vite HMR** — `npm run dev` (hot module replacement for CSS/JS)

### Production Build

```bash
npm run build
php artisan serve
```

---

## 🧪 Testing

```bash
# Run all tests
composer run test

# Or directly:
php artisan config:clear
php artisan test
```

Tests are located in the `tests/` directory and use **PHPUnit `^11.5`**.

## 🚀 Deploying on Render

This repository includes a `render.yaml` blueprint and `Dockerfile` for a Docker-based Render web service.

1. Create a new Render Blueprint from this repository.
2. Set the required secrets when prompted: `APP_URL`, `MONGODB_URI`, `MONGODB_DATABASE`, and `SUPER_ADMIN_SECRET_KEY`.
3. Deploy the `discount-pro` web service.

Render runs the app from the Docker image and executes `php artisan migrate --force` before each deploy.

---

## 🔒 Security Highlights

| Concern | Solution |
|---|---|
| **Privilege escalation** | `role` is not in `$fillable`; only `assignRole()` can change it |
| **CSRF protection** | Laravel's built-in CSRF token on all POST/PATCH/DELETE forms |
| **Rate limiting** | Validate endpoint: 30/min · Apply endpoint: 5/min |
| **Race conditions** | `apply()` re-validates atomically before committing any DB writes |
| **Password security** | Bcrypt hashing via Laravel's `'password' => 'hashed'` cast (`BCRYPT_ROUNDS=12`) |
| **Input validation** | All form submissions go through dedicated `FormRequest` classes |
| **Auth guard** | All non-public routes require `auth` middleware |
| **Admin guard** | All admin routes additionally require the `manager` middleware |
| **MongoDB injection** | Eloquent ORM abstracts all queries — no raw query strings used |

---

## 📐 Design System

The Tailwind configuration extends the default theme with a **violet brand palette** and custom animations:

**Brand colours:** `brand-50` → `brand-900` (violet scale based on `#8b5cf6`)

**Custom animations:** `fade-in`, `slide-up`, `slide-in-right`, `slide-in-left`, `scale-in`, `shimmer`, `pulse-slow`, `spin-slow`

**Typography:** Inter font (Google Fonts)

**Custom shadows:** `card` and `card-hover` for consistent elevation

---

## 📄 License

This project is open-source and available under the [MIT License](https://opensource.org/licenses/MIT).

---

*Built with ❤️ using Laravel 12 + MongoDB*
