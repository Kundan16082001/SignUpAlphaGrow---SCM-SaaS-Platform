<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        $unreadCount = Auth::user()->unreadNotifications()->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $link = $notification->data['link'] ?? '/dashboard';
        return redirect($link);
    }

    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    public function count()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function delete(string $id)
    {
        Auth::user()->notifications()->findOrFail($id)->delete();
        return redirect()->back();
    }

    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        return redirect()->back()->with('success', 'All notifications cleared');
    }
}