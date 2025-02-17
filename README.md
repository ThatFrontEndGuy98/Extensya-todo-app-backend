# Laravel Todo Application Backend API

A robust Laravel-based backend API for a Todo application featuring real-time notifications, advanced caching, and complete task management system.

## Features

- 🔐 Authentication & Authorization
  - JWT/Sanctum Authentication
  - Role-based Access Control (Admin/User)
  - Protected API Routes

- 📝 Task Management
  - CRUD Operations
  - Task Status Updates
  - Priority Management
  - Due Date Handling

- 🔔 Notifications System
  - Real-time Admin Notifications
  - User Notifications
  - Notification Status Management

- 💾 Data Management
  - Redis Caching
  - Database Optimization
  - Efficient Query Handling

## API Endpoints

### Authentication
```
POST /api/v1/register
POST /api/v1/login
POST /api/v1/logout
```

### User Tasks
```
GET    /api/v1/tasks
POST   /api/v1/tasks
GET    /api/v1/tasks/{id}
PUT    /api/v1/tasks/{id}
DELETE /api/v1/tasks/{id}
PATCH  /api/v1/tasks/{id}/status
```

### Admin Routes
```
GET    /api/v1/admin/tasks
POST   /api/v1/admin/tasks
GET    /api/v1/admin/tasks/{id}
PUT    /api/v1/admin/tasks/{id}
DELETE /api/v1/admin/tasks/{id}
GET    /api/v1/admin/tasks/by-user/{user}
```

### Notifications
```
POST   /api/v1/admin/notifications
GET    /api/v1/notifications
GET    /api/v1/notifications/unread-count
PATCH  /api/v1/notifications/{notification}/mark-as-read
DELETE /api/v1/notifications/{notification}
```

## Project Structure
```
app/
├── Events/
│   ├── TaskCreated.php
│   ├── TaskDeleted.php
│   └── TaskUpdated.php
├── Http/
│   ├── Controllers/
│   │   ├── API/V1/
│   │   │   ├── AuthController.php
│   │   │   ├── TaskController.php
│   │   │   └── AdminNotificationController.php
│   ├── Middleware/
│   │   ├── AdminMiddleware.php
│   │   └── ApiAuthentication.php
│   ├── Requests/
│   │   ├── DeleteTaskRequest.php
│   │   ├── ListTaskRequest.php
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   ├── StoreTaskRequest.php
│   │   └── UpdateTaskRequest.php
│   └── Resources/
│       ├── NotificationResource.php
│       ├── TaskResource.php
│       └── UserResource.php
├── Models/
│   ├── Notification.php
│   ├── Task.php
│   └── User.php
└── Listeners/
    └── SendTaskNotification.php
```

## Installation

1. Clone the repository
```bash
git clone https://github.com/ThatFrontEndGuy98/Extensya-todo-app-backend.git
cd Extensya-todo-app-backend
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
# Update database and other configurations in .env
```

4. Generate application key
```bash
php artisan key:generate
```

5. Run migrations
```bash
php artisan migrate
```

6. Start the server
```bash
php artisan serve
```

## Environment Variables

Make sure to set these in your .env file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Development

To run the development server:
```bash
php artisan serve
```

For database changes:
```bash
php artisan migrate:fresh --seed
```

## Testing API Endpoints

You can test the API endpoints using Postman or any API testing tool. Make sure to:
1. Include the Authorization header with your Bearer token
2. Set Content-Type to application/json
3. Use the correct HTTP method for each endpoint

## Security

This application implements:
- Sanctum authentication
- CSRF protection
- XSS prevention
- SQL injection prevention
- Rate limiting
- Input validation

## Requirements

- PHP >= 8.1
- Laravel 10.x
- MySQL/PostgreSQL
- Redis Server
- Composer

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
