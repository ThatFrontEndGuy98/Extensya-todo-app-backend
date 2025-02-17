<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $task;
    private $action;

    public function __construct(Task $task, string $action)
    {
        $this->task = $task;
        $this->action = $action;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $actionText = match($this->action) {
            'created' => 'A new task has been created',
            'updated' => 'A task has been updated',
            'deleted' => 'A task has been deleted',
            default => 'There has been a task update'
        };

        return (new MailMessage)
            ->subject("Task {$this->action}: {$this->task->title}")
            ->line($actionText)
            ->line("Task: {$this->task->title}")
            ->line("Status: {$this->task->status}")
            ->line("Priority: {$this->task->priority}")
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Thank you for using our application!');
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'action' => $this->action,
            'user_id' => $this->task->user_id,
            'user_name' => $this->task->user->name,
            'status' => $this->task->status,
            'priority' => $this->task->priority,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification' => [
                'task_id' => $this->task->id,
                'task_title' => $this->task->title,
                'action' => $this->action,
                'status' => $this->task->status,
                'priority' => $this->task->priority,
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
    }
}