<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', (string) auth()->user()->_id)
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        // Mark all as read on visit
        Notification::where('user_id', (string) auth()->user()->_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    /** AJAX: return latest 5 unread + total unread count for the bell dropdown. */
    public function bell()
    {
        $userId = (string) auth()->user()->_id;
        $unreadCount = Notification::where('user_id', $userId)->whereNull('read_at')->count();
        $recent      = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['_id', 'type', 'title', 'body', 'link', 'icon', 'color', 'read_at', 'created_at']);

        return response()->json([
            'unread_count' => $unreadCount,
            'recent'       => $recent->map(fn ($n) => [
                'id'         => (string) $n->_id,
                'title'      => $n->title,
                'body'       => $n->body,
                'link'       => $n->link,
                'icon'       => $n->icon,
                'color'      => $n->color,
                'read'       => $n->read_at !== null,
                'ago'        => $n->created_at?->diffForHumans() ?? '',
            ])->all(),
        ]);
    }

    public function markRead(string $id)
    {
        Notification::where('_id', $id)
            ->where('user_id', (string) auth()->user()->_id)
            ->update(['read_at' => now()]);
        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', (string) auth()->user()->_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
