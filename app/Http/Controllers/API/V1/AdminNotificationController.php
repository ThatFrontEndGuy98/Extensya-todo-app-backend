<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\User;
use App\Events\AdminNotificationSent;
use App\Http\Resources\NotificationResource;
use App\Events\NewNotification;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AdminNotificationController extends Controller
{
    public function store(Request $request)
    {

        // if (true) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthorized. Admin access required.'
        //     ], Response::HTTP_FORBIDDEN);
        // }


        $validator = Validator::make($request->all(), [
            'user_id' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|string|in:info,warning,success,error'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $notification = new Notification();
            $notification->type = $request->type;
            $notification->title = $request->title;
            $notification->message = $request->message;
            $notification->sender_id = Auth::id();
            $notification->user_id =  $request->recipient_id;
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating notification',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request)
    {
        $notifications = AdminNotification::query()
            ->when($request->user()->is_admin, function ($query) {
                $query->where('from_admin_id', Auth::id());
            }, function ($query) {
                $query->where('to_user_id', Auth::id());
            })
            ->with(['admin', 'user'])
            ->latest()
            ->paginate(15);

        return NotificationResource::collection($notifications);
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

        try {
            DB::beginTransaction();


            $notification = Notification::create([
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'priority' => $validated['priority'],
                'user_id' => $validated['recipient_id'],
                'sender_id' => auth()->id()
            ]);


            $notification = $notification->fresh();


            event(new NewNotification($notification));

            DB::commit();

            return response()->json([
                'message' => 'Notification sent successfully',
                'data' => $notification
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(AdminNotification $notification)
    {
        $this->authorize('update', $notification);
        
        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $notification = Notification::findOrFail($id);
            

            if ($notification->sender_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
            }
            
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notification'
            ]);
        }
    }

    public function getUnreadCount()
    {
        $count = AdminNotification::where('to_user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
