<?php 
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\Task;
use App\Models\User;

class CacheService
{
    const TASK_CACHE_KEY = 'tasks:user:';
    const USER_CACHE_KEY = 'users:';
    const CACHE_TTL = 3600; // 1 hour

    public static function getCachedUserTasks(int $userId)
    {
        return Cache::remember(self::TASK_CACHE_KEY . $userId, self::CACHE_TTL, function () use ($userId) {
            return Task::where('user_id', $userId)
                      ->with('user')
                      ->orderBy('created_at', 'desc')
                      ->get();
        });
    }

    public static function getCachedUser(int $userId)
    {
        return Cache::remember(self::USER_CACHE_KEY . $userId, self::CACHE_TTL, function () use ($userId) {
            return User::with('tasks')->find($userId);
        });
    }

    public static function invalidateUserTasks(int $userId)
    {
        Cache::forget(self::TASK_CACHE_KEY . $userId);
    }

    public static function invalidateUser(int $userId)
    {
        Cache::forget(self::USER_CACHE_KEY . $userId);
    }

    public static function cacheTasksList(array $filters = [])
    {
        $cacheKey = 'tasks:filtered:' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters) {
            $query = Task::query();
            
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }
            
            if (isset($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }
            
            return $query->with('user')->get();
        });
    }
}