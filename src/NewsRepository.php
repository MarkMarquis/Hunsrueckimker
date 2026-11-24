<?php
declare(strict_types=1);

namespace App;

use PDO;

final class NewsRepository
{
    public function __construct(private readonly PDO $db) {}

    public function published(int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM news WHERE published = 1 AND published_at <= date('now', 'localtime')
             ORDER BY published_at DESC, id DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM news ORDER BY published_at DESC, id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM news WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $stmt = $this->db->prepare(
                'UPDATE news SET title = ?, content = ?, image = ?, published = ?, published_at = ?,
                 updated_at = CURRENT_TIMESTAMP WHERE id = ?'
            );
            $stmt->execute([$data['title'], $data['content'], $data['image'], $data['published'], $data['published_at'], $id]);
            return $id;
        }
        $stmt = $this->db->prepare(
            'INSERT INTO news (title, content, image, published, published_at) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$data['title'], $data['content'], $data['image'], $data['published'], $data['published_at']]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM news WHERE id = ?')->execute([$id]);
    }
}
