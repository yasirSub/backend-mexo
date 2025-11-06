<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get all notifications for admin
     */
    public function index(Request $request)
    {
        // Get notifications for admin (where seller_id is null - these are admin notifications)
        // This includes both notifications with admin_id set and general admin notifications
        $query = Notification::whereNull('seller_id');

        // Filter by type if provided
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $isRead = filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN);
            $query->where('is_read', $isRead);
        }

        // Search by title or message
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('message', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        
        if ($sortBy === 'date') {
            $sortBy = 'created_at';
        } elseif ($sortBy === 'status') {
            $sortBy = 'is_read';
        }
        
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->query('per_page', 50);
        $notifications = $query->paginate($perPage);

        // Transform the data
        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'admin_id' => $notification->admin_id,
                'seller_id' => $notification->seller_id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => $notification->data,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at ? $notification->read_at->toDateTimeString() : null,
                'created_at' => $notification->created_at ? $notification->created_at->toDateTimeString() : now()->toDateTimeString(),
                'updated_at' => $notification->updated_at ? $notification->updated_at->toDateTimeString() : now()->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get notification statistics
     */
    public function statistics(Request $request)
    {
        $query = Notification::whereNull('seller_id');

        $total = $query->count();
        $unread = (clone $query)->where('is_read', false)->count();
        $read = (clone $query)->where('is_read', true)->count();

        // Count by type
        $byType = (clone $query)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Recent notifications (last 7 days)
        $recent = (clone $query)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'unread' => $unread,
                'read' => $read,
                'by_type' => $byType,
                'recent' => $recent,
            ],
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::whereNull('seller_id')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::whereNull('seller_id')
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'id' => $notification->id,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $updated = Notification::whereNull('seller_id')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} notifications marked as read",
            'data' => [
                'updated_count' => $updated,
            ],
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $notification = Notification::whereNull('seller_id')
            ->findOrFail($id);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request)
    {
        $deleted = Notification::whereNull('seller_id')
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} notifications deleted",
            'data' => [
                'deleted_count' => $deleted,
            ],
        ]);
    }

    /**
     * Create a test notification (for development/testing)
     */
    public function createTest(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|in:info,success,warning,error,order,product,seller,payment',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $admin = $request->user();
        
        $notification = Notification::create([
            'admin_id' => $admin->id ?? null,
            'seller_id' => null,
            'type' => $request->type ?? 'info',
            'title' => $request->title,
            'message' => $request->message,
            'data' => $request->data ?? null,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test notification created',
            'data' => [
                'id' => $notification->id,
                'admin_id' => $notification->admin_id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->toDateTimeString(),
            ],
        ], 201);
    }
}

