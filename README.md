# Laravel Todo Application with Real-Time Notifications

A comprehensive Laravel-based Todo application featuring real-time notifications, advanced caching, and a complete REST API. The application includes both admin and user interfaces with Vue.js frontend.

## Features

- 🔐 User Authentication & Authorization
- 📝 Task Management (CRUD operations)
- 🔔 Real-time Notifications using Laravel Echo and Pusher
- 💾 Advanced Caching with Redis
- 🎯 REST API
- 👑 Admin Dashboard
- 🎨 Vue.js Frontend with Tailwind CSS

## Technical Stack

### Backend
- Laravel 10.x
- PHP 8.1+
- MySQL/PostgreSQL
- Redis (for caching)
- Pusher (for real-time notifications)

### Frontend
- Vue.js 3
- Tailwind CSS
- Axios for API calls

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL/PostgreSQL
- Redis Server
- Pusher Account

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd <project-folder>
```

2. Install PHP dependencies
```bash
composer install
```

3. Install Node.js dependencies
```bash
npm install
```

4. Set up environment variables
```bash
cp .env.example .env
# Configure your database, Redis, and Pusher credentials in .env
```

5. Generate application key
```bash
php artisan key:generate
```

6. Run migrations
```bash
php artisan migrate
```

7. Build frontend assets
```bash
npm run build
```

## Development Setup

1. Start Laravel development server
```bash
php artisan serve
```

2. Watch for frontend changes
```bash
npm run dev
```

## API Routes

### Authentication
- POST `/api/v1/register` - Register new user
- POST `/api/v1/login` - User login
- POST `/api/v1/logout` - User logout

### Tasks
- GET `/api/v1/tasks` - List user's tasks
- POST `/api/v1/tasks` - Create new task
- GET `/api/v1/tasks/{id}` - Get task details
- PUT `/api/v1/tasks/{id}` - Update task
- DELETE `/api/v1/tasks/{id}` - Delete task
- PATCH `/api/v1/tasks/{id}/status` - Toggle task status

### Admin Routes
- GET `/api/v1/admin/tasks` - List all tasks
- GET `/api/v1/admin/users` - List all users
- POST `/api/v1/admin/notifications` - Send admin notifications

## Project Structure

### Backend
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── API/V1/
│   │   │   ├── AuthController.php
│   │   │   ├── TaskController.php
│   │   │   └── AdminNotificationController.php
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Events/
└── Listeners/
```

### Frontend
```
src/
├── components/
├── views/
├── router/
└── store/
```

## Security

The application implements several security measures:
- Sanctum authentication for API
- Route middleware for access control
- CSRF protection
- XSS prevention
- Input validation

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
