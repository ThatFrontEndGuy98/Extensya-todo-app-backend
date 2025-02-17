<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AdminNotification;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminNotificationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, AdminNotification $notification)
    {
        return $user->id === $notification->to_user_id || 
               $user->id === $notification->from_admin_id;
    }

    public function create(User $user)
    {
        return $user->is_admin;
    }

    public function update(User $user, AdminNotification $notification)
    {
        return $user->id === $notification->to_user_id;
    }

    public function delete(User $user, AdminNotification $notification)
    {
        return $user->id === $notification->from_admin_id || 
               $user->id === $notification->to_user_id;
    }
}