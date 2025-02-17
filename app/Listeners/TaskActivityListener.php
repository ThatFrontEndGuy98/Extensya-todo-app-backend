<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\TaskDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TaskActivityListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handleTaskCreated(TaskCreated $event)
    {

        $this->logActivity($event->task, 'created');
    }

    public function handleTaskUpdated(TaskUpdated $event)
    {

        $this->logActivity($event->task, 'updated');
    }


    public function handleTaskDeleted(TaskDeleted $event)
    {

        $this->logActivity($event->task, 'deleted');
    }

    /**
     * Log the activity.
     */
    private function logActivity($task, $action)
    {
        // 
    }
}