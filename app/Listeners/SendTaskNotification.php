<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskNotification;

class SendTaskNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle multiple events.
     */
    public function handle($event)
    {
        $task = $event->task;
        $action = $this->determineAction($event);
        $users = $this->getRelevantUsers($task);

        foreach ($users as $user) {
            Notification::send($user, new TaskNotification($task, $action));
        }

        // Broadcast to relevant channels
        broadcast(new TaskNotification($task, $action))->toOthers();
    }

    /**
     * Determine the action based on event type.
     */
    private function determineAction($event): string
    {
        return match (true) {
            $event instanceof TaskCreated => 'created',
            $event instanceof TaskUpdated => 'updated',
            $event instanceof TaskDeleted => 'deleted',
            default => 'modified'
        };
    }

    /**
     * Get users who should be notified.
     */
    private function getRelevantUsers($task)
    {

        return User::where('id', $task->user_id)
            ->orWhere('role', 'admin')
            ->get();
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception)
    {

        \Log::error('Failed to send task notification', [
            'task_id' => $event->task->id,
            'error' => $exception->getMessage()
        ]);
    }
}