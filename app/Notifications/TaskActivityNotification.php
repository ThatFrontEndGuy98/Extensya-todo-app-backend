<?php

namespace App\Notifications;

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TaskActivityListener implements ShouldQueue
{
    use InteractsWithQueue;


    public function handle($event)
    {
        $task = $event->task;
        $action = $this->determineAction($event);
        activity()
            ->performedOn($task)
            ->causedBy(auth()->user())
            ->withProperties([
                'action' => $action,
                'task_title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority
            ])
            ->log($action . '_task');

        $this->updateTaskStatistics($task);
    }

    private function updateTaskStatistics($task)
    {

        cache()->tags(['task_stats'])->put(
            'user_' . $task->user_id . '_task_count',
            $task->user->tasks()->count(),
            now()->addHours(24)
        );


        $totalTasks = $task->user->tasks()->count();
        $completedTasks = $task->user->tasks()->where('status', 'completed')->count();
        
        if ($totalTasks > 0) {
            $completionRate = ($completedTasks / $totalTasks) * 100;
            cache()->tags(['user_stats'])->put(
                'user_' . $task->user_id . '_completion_rate',
                $completionRate,
                now()->addHours(24)
            );
        }
    }

    private function determineAction($event): string
    {
        $class = get_class($event);
        return strtolower(substr($class, strrpos($class, '\\') + 1));
    }
}