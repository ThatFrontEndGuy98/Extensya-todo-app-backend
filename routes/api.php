
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\TaskController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AdminNotificationController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});


Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // User routes
    Route::get('user/profile', [AuthController::class, 'profile']);
    Route::put('user/profile', [AuthController::class, 'updateProfile']);
    Route::post('logout', [AuthController::class, 'logout']);
    
    // Task routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index']);
        Route::post('/', [TaskController::class, 'store']);
        Route::get('/{task}', [TaskController::class, 'show']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroy']);
        Route::patch('/{task}/status', [TaskController::class, 'toggleStatus']);
    });

    // Admin only routes
    Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        // Admin User management
        Route::get('users', [AuthController::class, 'listUsers']);
        Route::post('users', [AuthController::class, 'adminRegister']);
        Route::get('users/{user}', [AuthController::class, 'showUser']);
        Route::put('users/{user}', [AuthController::class, 'updateUser']);
        Route::delete('users/{user}', [AuthController::class, 'deleteUser']);
        
        //Admin Task management (CRUD of ALL TASKS AVAILABLE )
        Route::prefix('tasks')->group(function () {
            Route::get('/', [TaskController::class, 'adminIndex']);
            Route::post('/', [TaskController::class, 'adminStore']); 
            Route::get('/{task}', [TaskController::class, 'adminShow']); 
            Route::put('/{task}', [TaskController::class, 'adminUpdate']);
            Route::delete('/{task}', [TaskController::class, 'adminDestroy']); 
            Route::patch('/{task}/status', [TaskController::class, 'adminUpdateStatus']); 
            Route::get('/by-user/{user}', [TaskController::class, 'adminUserTasks']);  
        });

        // Admin Notifications management
        Route::post('notifications', [AuthController::class, 'sendNotification']);
        Route::get('notifications/history', [AuthController::class, 'notificationHistory']);
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/admin/notifications', [AdminNotificationController::class, 'store']);
    });
    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/notifications', [AdminNotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [AdminNotificationController::class, 'getUnreadCount']);
        Route::patch('/notifications/{notification}/mark-as-read', [AdminNotificationController::class, 'markAsRead']);
        Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy']);
    });
});
// Health check route
Route::get('health', function () {
    return response()->json(['status' => 'healthy']);
});