# Enterprise Laravel MVC Backend with MongoDB & OpenAPI / Swagger

A production-ready Laravel MVC backend built with **MongoDB** (`mongodb/laravel-mongodb`), **Role-Based Access Control (RBAC)** (Admin vs. Customer), **28+ RESTful CRUD endpoints**, interactive **OpenAPI 3.0 / Swagger UI** documentation, and an **Admin Dashboard** powered by Blade and Tailwind CSS.

---

## Architecture & System Overview

- **Framework**: Laravel 10.x / 11.x (Strict MVC Pattern)
- **Database**: MongoDB (v6.0 / v7.0 or MongoDB Atlas)
- **ORM / ODM**: `mongodb/laravel-mongodb` (Official MongoDB Laravel Integration)
- **Authentication**: Laravel Sanctum (API Bearer Tokens) + Laravel Session Guard (Web Admin)
- **API Documentation**: OpenAPI 3.0 / L5-Swagger (`darkaonline/l5-swagger`)
- **Dashboard UI**: Blade templates styled with Tailwind CSS

---

## Directory Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php          # Register, Login, Logout, Profile
│   │   │   │   ├── UserController.php          # User CRUD & Role Management
│   │   │   │   ├── CategoryController.php      # Category Taxonomy CRUD
│   │   │   │   ├── ProductController.php       # Product Catalog & Stock Control
│   │   │   │   ├── OrderController.php         # Customer Orders & Fulfillment
│   │   │   │   └── AdminDashboardController.php# KPI Metrics & System Audit Trail
│   │   │   ├── Web/
│   │   │   │   ├── WebAuthController.php       # Web Session Login & Logout
│   │   │   │   └── WebAdminController.php      # Admin Dashboard Blade Controller
│   │   │   └── Controller.php                  # Base Controller with Swagger Info
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php             # Strict Role: admin verification
│   │   │   └── Authenticate.php                # Smart JSON/Web auth redirector
│   │   ├── Requests/
│   │   │   ├── RegisterRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── CategoryRequest.php
│   │   │   ├── ProductRequest.php
│   │   │   └── OrderRequest.php
│   │   └── Kernel.php                          # Middleware pipeline registration
│   ├── Models/
│   │   ├── User.php                            # Extends MongoDB Authenticatable
│   │   ├── Category.php                        # Extends MongoDB Eloquent Model
│   │   ├── Product.php                         # Extends MongoDB Eloquent Model
│   │   ├── Order.php                           # Extends MongoDB Eloquent Model
│   │   └── AuditLog.php                        # Extends MongoDB Eloquent Model
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── AuthServiceProvider.php
│       └── RouteServiceProvider.php
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php                            # MongoDB connection definition
│   └── l5-swagger.php                          # OpenAPI 3.0 configuration
├── database/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── AdminUserSeeder.php
│       └── StoreSeeder.php
├── public/
│   └── docs/
│       └── index.html                          # Standalone Swagger UI viewer
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php                   # Tailwind CSS base layout
│       ├── auth/
│       │   └── login.blade.php                 # Web admin login screen
│       └── admin/
│           └── dashboard.blade.php             # Real-time metrics & management UI
├── routes/
│   ├── api.php                                 # 28 API routes (/api/v1/*)
│   └── web.php                                 # Web login, admin UI, docs redirect
├── storage/
│   └── api-docs/
│       └── api-docs.json                       # OpenAPI 3.0 JSON specification
├── .env.example
├── composer.json
└── README.md
```

---

## Step-by-Step Setup Instructions

### 1. Prerequisites
- **PHP 8.2+** with `openssl`, `mbstring`, `curl`, and `mongodb` extensions enabled.
- **Composer 2.x**.
- **MongoDB Server** running locally (`mongodb://127.0.0.1:27017`) or a free [MongoDB Atlas Cluster](https://www.mongodb.com/cloud/atlas).

> **Enabling the MongoDB PHP Extension on Windows**:
> 1. Download the DLL matching your PHP version (e.g. `php_mongodb.dll` x64 NTS/TS) from [PECL MongoDB](https://pecl.php.net/package/mongodb).
> 2. Place `php_mongodb.dll` into your PHP extension folder (e.g., `C:\php\ext\`).
> 3. Add `extension=mongodb` to your `php.ini`.
> 4. Verify with `php -m | findstr mongodb`.

### 2. Install Composer Dependencies
Navigate to the `backend` directory and run:
```bash
composer install --ignore-platform-req=ext-mongodb
```
*(Use `--ignore-platform-req=ext-mongodb` if you haven't enabled the DLL yet).*

### 3. Environment Configuration (`.env`)
Copy the template configuration:
```bash
cp .env.example .env
```
Ensure your MongoDB credentials match your environment:
```env
APP_NAME="Laravel MongoDB Backend"
APP_ENV=local
APP_KEY=base64:r8q+a28f/X+m9YxXJ8m8Z5w9Xy9U8xQ0w3k1s7m9o2s=
APP_DEBUG=true
APP_URL=http://localhost:8000

# MongoDB Database Configuration
DB_CONNECTION=mongodb
MONGODB_URI="mongodb://127.0.0.1:27017"
MONGODB_DATABASE=ecommerce_db
MONGODB_USERNAME=
MONGODB_PASSWORD=

# If using MongoDB Atlas:
# MONGODB_URI="mongodb+srv://<username>:<password>@cluster0.mongodb.net/?retryWrites=true&w=majority"
# MONGODB_DATABASE=ecommerce_db

L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_CONST_HOST=http://localhost:8000/api/v1
```

Generate a fresh application key if needed:
```bash
php artisan key:generate
```

### 4. Seed MongoDB with Demo Data
Populate MongoDB collections with demo administrator accounts, customers, product catalog, and orders:
```bash
php artisan db:seed
```

### 5. Generate API Documentation
Compile and regenerate the OpenAPI 3.0 documentation:
```bash
php artisan l5-swagger:generate
```

### 6. Start the Local Server
```bash
php artisan serve
```
The backend will be live at `http://localhost:8000`.

---

## Default Credentials

| Role | Email | Password | Access Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@example.com` | `Admin123!` | Full CRUD on Users, Products, Categories, Stock, Status, Metrics |
| **Customer** | `customer@example.com` | `Customer123!` | Public Catalog, Cart Checkout, Order History, Profile |
| **Customer** | `john.doe@example.com` | `Customer123!` | Public Catalog, Cart Checkout, Order History, Profile |

---

## API & Web Routes Specification (32 Routes)

### Web Interface Routes (`routes/web.php`)
| Method | URI | Description | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/` | Redirects to interactive API Documentation | None |
| `GET` | `/login` | Admin login page (Blade template) | `guest` |
| `POST` | `/login` | Submit login authentication | `guest` |
| `POST` | `/logout` | Invalidate web session & logout | `auth` |
| `GET` | `/admin/dashboard`| Admin management console (KPIs, orders, stock) | `auth`, `admin` |

### API Endpoints (`routes/api.php` under `/api/v1`)

#### 1. Authentication & Profile
| Method | URI | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth/register` | Register a new customer account | No |
| `POST` | `/api/v1/auth/login` | Login and issue Bearer token | No |
| `POST` | `/api/v1/auth/logout` | Revoke active Bearer token | `auth:sanctum` |
| `GET` | `/api/v1/auth/me` | Fetch authenticated user profile | `auth:sanctum` |
| `PUT` | `/api/v1/auth/profile` | Update profile information | `auth:sanctum` |

#### 2. User Management (Admin Only)
| Method | URI | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/users` | List users with pagination and search | `auth:sanctum`, `admin` |
| `POST` | `/api/v1/users` | Create user/staff account directly | `auth:sanctum`, `admin` |
| `GET` | `/api/v1/users/{id}` | Get single user details by ID | `auth:sanctum`, `admin` |
| `PUT` | `/api/v1/users/{id}` | Update user details & role | `auth:sanctum`, `admin` |
| `DELETE`| `/api/v1/users/{id}` | Delete user record from MongoDB | `auth:sanctum`, `admin` |

#### 3. Categories Management
| Method | URI | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/categories` | List active categories & subcategories | Public |
| `GET` | `/api/v1/categories/{id}` | Show category details with products | Public |
| `POST` | `/api/v1/categories` | Create new category | `auth:sanctum`, `admin` |
| `PUT` | `/api/v1/categories/{id}` | Update category details | `auth:sanctum`, `admin` |
| `DELETE`| `/api/v1/categories/{id}` | Delete category | `auth:sanctum`, `admin` |

#### 4. Product Catalog & Inventory
| Method | URI | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/products` | Search/filter products by price/cat/keyword | Public |
| `GET` | `/api/v1/products/{id}` | Show detailed product record | Public |
| `POST` | `/api/v1/products` | Create catalog product | `auth:sanctum`, `admin` |
| `PUT` | `/api/v1/products/{id}` | Update product information | `auth:sanctum`, `admin` |
| `DELETE`| `/api/v1/products/{id}` | Delete product from catalog | `auth:sanctum`, `admin` |
| `PATCH` | `/api/v1/products/{id}/stock` | Quick stock adjustment (set/inc/dec) | `auth:sanctum`, `admin` |

#### 5. Orders & Checkout
| Method | URI | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/orders` | Customer's orders (or all for Admin) | `auth:sanctum` |
| `POST` | `/api/v1/orders` | Place order (stock decrement & snapshot) | `auth:sanctum` |
| `GET` | `/api/v1/orders/{id}` | Show order details | `auth:sanctum` |
| `PATCH` | `/api/v1/orders/{id}/status`| Update order status (shipped, delivered, etc) | `auth:sanctum`, `admin` |
| `DELETE`| `/api/v1/orders/{id}` | Cancel pending order | `auth:sanctum` |

#### 6. Dashboard Metrics & Audit Trail
| Method | URI | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/admin/dashboard/stats` | High-level business KPIs & revenue | `auth:sanctum`, `admin` |
| `GET` | `/api/v1/admin/audit-logs` | Retrieve chronological audit trail | `auth:sanctum`, `admin` |

---

## Interactive API Documentation (Swagger / OpenAPI 3.0)

Interactive documentation is accessible via two avenues:
1. **L5-Swagger Route**: `http://localhost:8000/api/documentation`
2. **Standalone HTML UI**: `http://localhost:8000/docs/index.html` (or by opening `public/docs/index.html` directly in any web browser).

### Authorizing in Swagger UI:
1. Call `POST /api/v1/auth/login` with `admin@example.com` and `Admin123!`.
2. Copy the returned `data.token`.
3. Click the **Authorize** (lock icon) button at the top right of the Swagger UI.
4. Paste the token as `Bearer <your_token>` and click **Authorize**.
5. All protected endpoints are now fully testable directly in the browser!

