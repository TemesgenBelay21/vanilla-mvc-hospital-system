<?php

/**
 * Notification model — in-app notifications surfaced via the
 * header bell on every dashboard.
 */

declare(strict_types=1);

class Notification
{
    public static function create(int $userId, string $title, string $message, ?string $link = null): int
    {
        $stmt = db()->prepare(
            'INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $message, $link]);
        return (int) db()->lastInsertId();
    }

    public static function forUser(int $userId, int $limit = 10): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public static function markRead(int $userId, int $id): void
    {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        $stmt = db()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
}