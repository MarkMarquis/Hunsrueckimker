<?php
declare(strict_types=1);

namespace App;

use PDO;

final class MessageRepository
{
    public function __construct(private readonly PDO $db) {}

    public function create(array $d): int
    {
        $this->db->prepare(
            'INSERT INTO messages (name, email, phone, subject, message, type, res_date, res_time, persons, area)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $d['name'], $d['email'], $d['phone'], $d['subject'], $d['message'],
            $d['type'] ?? 'anfrage', $d['res_date'] ?? '', $d['res_time'] ?? '', (int) ($d['persons'] ?? 0), $d['area'] ?? '',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function setMailError(int $id, string $error): void
    {
        $this->db->prepare('UPDATE messages SET mail_error = ? WHERE id = ?')->execute([mb_substr($error, 0, 500), $id]);
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM messages ORDER BY created_at DESC, id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM messages WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function markRead(int $id, bool $read = true): void
    {
        $this->db->prepare('UPDATE messages SET is_read = ? WHERE id = ?')->execute([(int) $read, $id]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
    }

    public function unreadCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    }
}
