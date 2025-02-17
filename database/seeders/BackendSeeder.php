<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;

class BackendSeeder extends Seeder
{
    public function run(): void
    {

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);


        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            

            $tasks = [
                [
                    'title' => 'Complete Project Documentation',
                    'description' => 'Write comprehensive documentation for the new feature',
                    'priority' => 'high',
                    'status' => 'pending',
                    'due_date' => now()->addDays(7),
                ],
                [
                    'title' => 'Review Pull Requests',
                    'description' => 'Review and merge pending pull requests',
                    'priority' => 'medium',
                    'status' => 'in_progress',
                    'due_date' => now()->addDays(3),
                ],
                [
                    'title' => 'Weekly Team Meeting',
                    'description' => 'Prepare and attend weekly team sync',
                    'priority' => 'low',
                    'status' => 'completed',
                    'due_date' => now()->addDays(1),
                ],
                [
                    'title' => 'System Backup',
                    'description' => 'Perform system backup and verify integrity',
                    'priority' => 'high',
                    'status' => 'pending',
                    'due_date' => now()->addDays(2),
                ],
                [
                    'title' => 'Client Presentation',
                    'description' => 'Prepare and deliver client presentation',
                    'priority' => 'high',
                    'status' => 'in_progress',
                    'due_date' => now()->addDays(5),
                ]
            ];

            foreach ($tasks as $taskData) {
                $task = new Task($taskData);
                $task->user_id = $user->id;
                $task->save();
            }
        }


        $adminTasks = [
            [
                'title' => 'Review Security Policies',
                'description' => 'Conduct monthly security policy review',
                'priority' => 'high',
                'status' => 'pending',
                'due_date' => now()->addDays(10),
            ],
            [
                'title' => 'System Update Planning',
                'description' => 'Plan next system update rollout',
                'priority' => 'medium',
                'status' => 'in_progress',
                'due_date' => now()->addDays(15),
            ]
        ];

        foreach ($adminTasks as $taskData) {
            $task = new Task($taskData);
            $task->user_id = $admin->id;
            $task->save();
        }
    }
}