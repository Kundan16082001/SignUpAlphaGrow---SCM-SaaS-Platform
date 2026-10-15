<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Create a notification for a user (or all admins)
     */
    public static function create(
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null,
        ?int $userId = null,
        bool $notifyAllAdmins = false
    ): void {
        $tenantId = Auth::id() ? Auth::user()->tenant_id : 1;

        if ($notifyAllAdmins) {
            $users = User::where('tenant_id', $tenantId)
                ->where('role', 'admin')
                ->get();
            foreach ($users as $user) {
                self::createForUser($user->id, $title, $message, $type, $link);
            }
        } elseif ($userId) {
            self::createForUser($userId, $title, $message, $type, $link);
        } else {
            // Notify current user
            if (Auth::check()) {
                self::createForUser(Auth::id(), $title, $message, $type, $link);
            }
        }
    }

    private static function createForUser(int $userId, string $title, string $message, string $type, ?string $link): void
    {
        DB::table('notifications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\SystemNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $userId,
            'data' => json_encode([
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'link' => $link,
                'icon' => self::getIcon($type),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private static function getIcon(string $type): string
    {
        return match($type) {
            'success' => 'bi-check-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'danger' => 'bi-x-circle-fill',
            'info' => 'bi-info-circle-fill',
            'order' => 'bi-cart-check-fill',
            'stock' => 'bi-box-seam-fill',
            'payment' => 'bi-cash-coin',
            default => 'bi-bell-fill',
        };
    }

    // ============ CONVENIENCE METHODS ============
    
    public static function lowStock(string $productName, int $currentQty, int $reorderLevel): void
    {
        self::create(
            title: "Low Stock: {$productName}",
            message: "Only {$currentQty} units left (reorder level: {$reorderLevel})",
            type: 'stock',
            link: '/operations/inventory',
            notifyAllAdmins: true
        );
    }

    public static function newOrder(string $orderNo, string $customerName, float $total): void
    {
        self::create(
            title: "New Order: {$orderNo}",
            message: "Order from {$customerName} worth ₹" . number_format($total),
            type: 'order',
            link: '/operations/orders',
            notifyAllAdmins: true
        );
    }

    public static function paymentReceived(string $invoiceNo, float $amount): void
    {
        self::create(
            title: "Payment Received",
            message: "₹" . number_format($amount) . " received for invoice {$invoiceNo}",
            type: 'payment',
            link: '/finance-hr/finance',
            notifyAllAdmins: true
        );
    }

    public static function transferCompleted(string $transferNo, string $source, string $dest): void
    {
        self::create(
            title: "Transfer Completed",
            message: "Transfer {$transferNo} from {$source} to {$dest} received",
            type: 'success',
            link: '/operations/warehouse-transfers',
            notifyAllAdmins: true
        );
    }
}