<?php

namespace App\Http\Controllers\AdminDashboard;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AdminNotificationController extends Controller
{

    public function index()
    {
        $admin = Admin::find(auth()->id());

        // $admin = auth('admin')->id();
        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }
        return response()->json([
            "notifications" => $admin->notifications
        ]);
    }

    public function unread()
    {
        $admin = auth('admin')->user();
        return response()->json([
            "notifications" => $admin->unreadNotifications
        ]);
    }

    public function markReadOne($id)
    {
        $notification = DB::table('notifications')->find($id);
        if ($notification) {
            DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);
            return response()->json([
                "message" => "تم قراءة الإشعار بنجاح."
            ]);
        } else {
            return response()->json([
                "message" => "الإشعار غير موجود."
            ], 404);
        }
    }

    public function markRead()
    {
        $admin = auth('admin')->user();
        foreach ($admin->unreadNotifications as $notification) {
            $notification->markAsRead();
        }
        return response()->json([
            "message" => "تم قراءة جميع الإشعارات بنجاح."
        ]);
    }

    public function delete()
    {
        $admin = auth('admin')->user();
        $admin->notifications()->delete();
        return response()->json([
            "message" => "تم حذف جميع الإشعارات بنجاح."
        ]);
    }

    public function deleteOne($id)
    {
        DB::table('notifications')->where('id', '=', $id)->delete();
        return response()->json([
            "message" => "تم حذف الإشعار بنجاح."
        ]);
    }
}
