<?php

namespace App\Http\Controllers\API\V1;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\NotificationResource;
use App\Events\NewNotification;
use App\Models\Notification;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Create token for the new user
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Check email
        $user = User::where('email', $validated['email'])->first();

        // Check password
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ],
            'token' => $token,
        ]);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Password reset link sent to email'
            ]);
        }

        return response()->json([
            'message' => 'Unable to send reset link',
            'error' => __($status)
        ], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'message' => 'Unable to reset password',
            'error' => __($status)
        ], 400);
    }


    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }


    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => ['sometimes', 'required', 'confirmed', PasswordRule::defaults()],
        ]);

        DB::beginTransaction();
        try {

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }


            if (isset($validated['new_password'])) {
                $user->password = Hash::make($validated['new_password']);
            }

            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
    public function adminRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => 'password',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User Craeted successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function listUsers(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = User::query();


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }


        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($perPage);

        return response()->json([
            'users' => $users,
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }


    public function showUser(User $user)
    {

        $user->load('tasks');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'tasks_count' => $user->tasks->count(),
                'tasks' => $user->tasks
            ]
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'is_admin' => 'sometimes|required|boolean',
            'password' => ['sometimes', 'required', 'confirmed', PasswordRule::defaults()],
        ]);

        DB::beginTransaction();
        try {
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }
            
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            
            if (isset($validated['is_admin'])) {
                // Prevent removing admin status from the last admin
                if (!$validated['is_admin'] && $user->is_admin) {
                    $adminCount = User::where('is_admin', true)->count();
                    if ($adminCount <= 1) {
                        throw new \Exception('Cannot remove admin status from the last administrator');
                    }
                }
                $user->is_admin = $validated['is_admin'];
            }

            if (isset($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            // Revoke all tokens if admin status changed or password updated
            if (isset($validated['is_admin']) || isset($validated['password'])) {
                $user->tokens()->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'updated_at' => $user->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'User update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteUser(User $user)
    {

        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the last administrator'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {

            $user->tasks()->delete();
            

            $user->tokens()->delete();
            

            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'User deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,warning,success,error',
            'priority' => 'required|in:low,medium,high'
        ]);

        DB::beginTransaction();
        try {
            $admin = $request->user();
            

            $notification = Notification::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'priority' => $validated['priority'],
                'sender_id' => $admin->id,
                'user_id' => $validated['recipient_id']
            ]);

            if (!$notification) {
                throw new \Exception('Failed to create notification record');
            }


            $notification->refresh();


            broadcast(new NewNotification($notification))->toOthers();

            DB::commit();

            return response()->json([
                'message' => 'Notification sent successfully',
                'notification' => new NotificationResource($notification)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Notification sending failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);

            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function notificationHistory(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $type = $request->input('type');
        $priority = $request->input('priority');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $userId = $request->input('user_id');

        $query = Notification::with(['sender', 'user'])
            ->orderBy('created_at', 'desc');


        if ($type) {
            $query->where('type', $type);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notifications = $query->paginate($perPage);

        return NotificationResource::collection($notifications)
            ->additional([
                'meta' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'filters' => [
                        'type' => $type,
                        'priority' => $priority,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'user_id' => $userId
                    ]
                ]
            ]);
    }





















}