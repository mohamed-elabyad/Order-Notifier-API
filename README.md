# Order Notifier API

A production-ready RESTful API for order management with real-time push notifications, built with Laravel 12. This system enables businesses to track orders, manage their lifecycle, and automatically notify users via Firebase Cloud Messaging (FCM) when order statuses change.

## 🚀 Features

### Core Functionality
- **Order Management**: Create, read, update, and query orders with comprehensive filtering capabilities
- **Real-time Notifications**: Automatic push notifications via FCM when order status changes
- **Device Token Management**: Register and manage FCM device tokens for cross-platform support (Android, iOS, Web)
- **Advanced Filtering**: Pipeline-based query system supporting status filters, amount ranges, date ranges, search, and sorting
- **Role-based Access Control**: Fine-grained permission system using Laravel Sanctum with ability-based tokens

### Technical Highlights
- **Laravel 12**: Built on the latest Laravel framework with modern PHP 8.2
- **Laravel Sanctum**: Secure API authentication with scoped abilities
- **Firebase Cloud Messaging**: Enterprise-grade push notification delivery
- **Queue System**: Asynchronous notification processing for improved performance
- **Pipeline Pattern**: Clean, extensible filtering architecture
- **API Documentation**: Auto-generated Swagger/OpenAPI documentation
- **Comprehensive Testing**: Pest-based test suite for quality assurance

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.3+
- Firebase Cloud Messaging credentials (service account JSON)

## 🔧 Installation

### 1. Clone the repository
```bash
git clone <repository-url>
cd Order_Notifier
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:
- Database connection settings
- Firebase service account path (`FIREBASE_CREDENTIALS`)
- Queue connection (recommended: `database` or `redis`)

### 4. Set up database
```bash
php artisan migrate
php artisan db:seed
```

### 5. Start the development server
```bash
# Start Laravel server and queue worker
php artisan serve
php artisan queue:work
```

## 📡 API Documentation

The API is fully documented using Swagger/OpenAPI. After starting the server, access the interactive documentation at:

```
http://localhost:8000/api/docs
```

## 🔐 Authentication

All API endpoints (except registration and login) require Bearer token authentication. Tokens are issued with specific abilities that control access to different resources:

### Available Abilities
- `orders:read` - Read orders
- `orders:write` - Create and update orders
- `notify:send` - Send notifications
- `devices:write` - Manage device tokens

## 📝 API Examples

### Authentication

#### Register a new user
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Sam",
    "email": "sam@example.com",
    "password": "secret",
    "device_name": "postman",
    "abilities": ["orders:read", "orders:write", "notify:send", "devices:write"]
  }'
```

#### Login
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "sam@example.com",
    "password": "secret",
    "device_name": "mobile",
    "abilities": ["orders:read", "orders:write"]
  }'
```

### Orders

#### Create a new order
```bash
curl -X POST http://localhost:8000/api/v1/orders \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "ORD-1001",
    "amount_decimal": "199.99",
    "status": "placed",
    "placed_at": "2025-10-29T10:00:00Z"
  }'
```

#### List orders with filters (pipeline)
```bash
curl "http://localhost:8000/api/v1/orders?status=placed,processing&min_amount=100&sort=-placed_at" \
  -H "Authorization: Bearer {TOKEN}"
```

#### List orders with search query
```bash
curl "http://localhost:8000/api/v1/orders?q=ORD-1001&sort=-placed_at" \
  -H "Authorization: Bearer {TOKEN}"
```


#### Update order status (triggers queued FCM notification)
```bash
curl -X PATCH http://localhost:8000/api/v1/orders/{id} \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "shipped"
  }'
```

### Device Tokens

#### Register device token
```bash
curl -X POST http://localhost:8000/api/v1/devices \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "fcm_token_here",
    "platform": "android"
  }'
```

#### Delete device token
```bash
curl -X DELETE http://localhost:8000/api/v1/devices/{id} \
  -H "Authorization: Bearer {TOKEN}"
```

### Notifications

#### Manually trigger push notification for an order
```bash
curl -X POST http://localhost:8000/api/v1/orders/{id}/notify \
  -H "Authorization: Bearer {TOKEN}"
```

## 🔍 Query Parameters

### Order Listing Filters

The order listing endpoint supports the following query parameters:

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Comma-separated status filters | `status=placed,processing` |
| `min_amount` | number | Minimum order amount | `min_amount=100` |
| `max_amount` | number | Maximum order amount | `max_amount=500` |
| `q` | string | Search in order code | `q=ORD-1001` |
| `date_from` | date | Filter from date (YYYY-MM-DD) | `date_from=2025-10-01` |
| `date_to` | date | Filter to date (YYYY-MM-DD) | `date_to=2025-10-31` |
| `sort` | string | Sort field (prefix with `-` for descending) | `sort=-placed_at` |

### Order Status Values

- `placed`
- `processing`
- `shipped`
- `delivered`
- `cancelled`

## 🏗️ Architecture

### Pipeline Pattern
The order filtering system uses Laravel's Pipeline pattern for clean, extensible query filtering. Each filter is implemented as a separate pipe class:

- `StatusFilter` - Filter by order status
- `AmountRangeFilter` - Filter by amount range
- `SearchFilter` - Search in order codes
- `DateRangeFilter` - Filter by date range
- `SortPipe` - Sort results

### Queue System
Order status updates automatically dispatch `OrderStatusNotificationJob` to process notifications asynchronously, ensuring API responsiveness and reliable delivery.

### Authorization
The application uses Laravel Policies for resource authorization, ensuring users can only access their own orders and device tokens.

## 🧪 Testing

Run the test suite using Pest:

```bash
php artisan test
```

Run specific test files:

```bash
php artisan test tests/Feature/OrderTest.php
```

## 📦 Technologies

- **Laravel 12.36.1** - PHP Framework
- **Laravel Sanctum 4.2.0** - API Authentication
- **Firebase PHP SDK** - Cloud Messaging
- **Laravel Swagger (L5-Swagger)** - API Documentation
- **Pest 3.8.4** - Testing Framework
- **MySQL** - Database
- **PHP 8.2.12** - Runtime
