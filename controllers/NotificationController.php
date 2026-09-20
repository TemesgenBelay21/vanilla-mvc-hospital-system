<?php

/**
 * NotificationController — JSON endpoints powering the header notification
 * bell (list + mark read / mark all read).
 */

declare(strict_types=1);

class NotificationController
{
    public function index(): void
    {
        $user = require_login();
        json_response([
            'unread_count' => Notification::unreadCount((int) $user['id']),
            'items'        => Notification::forUser((int) $user['id'], 30),
        ]);
    }

    public function markRead(array $params): void
    {
        $user = require_login();
        csrf_check();
        Notification::markRead((int) $user['id'], (int) $params['id']);
        json_response(['ok' => true]);
    }

    public function markAllRead(): void
    {
        $user = require_login();
        csrf_check();
        Notification::markAllRead((int) $user['id']);
        json_response(['ok' => true]);
    }
}