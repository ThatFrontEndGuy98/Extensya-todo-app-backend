<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Services\CacheService;

use Illuminate\Support\Facades\DB;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::query()
            ->where('user_id', auth()->id())
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->when($request->priority, fn($query) => $query->where('priority', $request->priority))
            ->when($request->search, fn($query) => $query->where('title', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate($request->per_page ?? 10);
    
        return TaskResource::collection($tasks);
    }
    
    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();  
        
        $task = Task::create($validated);
        
        event(new TaskCreated($task));
        
        return new TaskResource($task);
    }
    
    public function show(Task $task)
    {

        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        return new TaskResource($task);
    }
    
    public function update(UpdateTaskRequest $request, Task $task)
    {

        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $task->update($request->validated());
        
        event(new TaskUpdated($task));
    
        return new TaskResource($task);
    }
    
    public function destroy(Task $task)
    {

        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        event(new TaskDeleted($task));
        
        $task->delete();
    
        return response()->json(['message' => 'Task deleted successfully']);
    }
    
    public function toggleStatus(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';
        $task->update(['status' => $newStatus]);
        
        event(new TaskUpdated($task));
    
        return new TaskResource($task);
    }

    /**
     * Display a listing of all tasks (Admin only)
     */
    public function adminIndex(Request $request)
    {
        // Get query parameters with defaults
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $status = $request->input('status');

        // Build query
        $query = Task::with('user');

        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        // Cache the paginated results
        $cacheKey = "admin_tasks_{$perPage}_{$sortBy}_{$sortOrder}_{$status}_" . $request->page;
        
        $tasks = Cache::remember($cacheKey, 3600, function () use ($query, $perPage) {
            return $query->paginate($perPage);
        });

        return TaskResource::collection($tasks);
    }

    /**
     * Store a new task (Admin only)
     */
    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'user_id' => 'required|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            $task = Task::create($validated);
            
            // Clear cache for task listings
            Cache::tags(['tasks', 'user_tasks_' . $validated['user_id']])->flush();
            
            // Fire task created event for notifications
            event(new TaskCreated($task));
            
            DB::commit();
            
            return new TaskResource($task);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating task'], 500);
        }
    }

    /**
     * Display the specified task (Admin only)
     */
    public function adminShow(Task $task)
    {
        $cacheKey = "task_{$task->id}";
        
        $task = Cache::remember($cacheKey, 3600, function () use ($task) {
            return $task->load('user');
        });

        return new TaskResource($task);
    }

    /**
     * Update the specified task (Admin only)
     */
    public function adminUpdate(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'due_date' => 'sometimes|required|date',
            'priority' => 'sometimes|required|in:low,medium,high',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'user_id' => 'sometimes|required|exists:users,id'
        ]);

        DB::beginTransaction();
        try {
            $task->update($validated);
            

            Cache::forget("task_{$task->id}");
            Cache::tags(['tasks', 'user_tasks_' . $task->user_id])->flush();
            

            event(new TaskUpdated($task));
            
            DB::commit();
            
            return new TaskResource($task->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating task'], 500);
        }
    }


    public function adminDestroy(Task $task)
    {
        DB::beginTransaction();
        try {
            $userId = $task->user_id;
            

            event(new TaskDeleted($task));
            
            $task->delete();
            

            // Cache::forget("task_{$task->id}");
            // Cache::tags(['tasks', 'user_tasks_' . $userId])->flush();
            
            DB::commit();
            
            return response()->json(['message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting task'], 500);
        }
    }


    public function adminUpdateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);

        DB::beginTransaction();
        try {
            $task->update($validated);

            Cache::forget("task_{$task->id}");
            Cache::tags(['tasks', 'user_tasks_' . $task->user_id])->flush();
            

            event(new TaskUpdated($task));
            
            DB::commit();
            
            return new TaskResource($task->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating task status'], 500);
        }
    }


    public function adminUserTasks(Request $request, User $user)
    {
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');
        
        $cacheKey = "user_{$user->id}_tasks_status_{$status}_page_" . $request->page;
        
        $tasks = Cache::remember($cacheKey, 3600, function () use ($user, $status, $perPage) {
            $query = $user->tasks();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            return $query->latest()->paginate($perPage);
        });

        return TaskResource::collection($tasks);
    }
}