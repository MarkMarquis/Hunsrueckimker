<?php
declare(strict_types=1);

namespace App;

use PDO;

/** Bilder des Bildwechslers im Banner. */
final class SlideRepository
{
    /** Erlaubte Bildausschnitte (CSS background-position) */
    public const POSITIONS = [
        'center'      => 'Mitte',
        '30% center'  => 'Links',
        '70% center'  => 'Rechts',
        '0% center'   => 'Ganz links',
        '100% center' => 'Ganz rechts',
        'center 20%'  => 'Oben',
        'center 80%'  => 'Unten',
    ];

    public function __construct(private readonly PDO $db) {}

    public function active(): array
    {
        return $this->db->query('SELECT * FROM slides WHERE active = 1 ORDER BY sort_order, id')->fetchAll();
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM slides ORDER BY sort_order, id')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM slides WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $path, string $alt = '', string $position = 'center', bool $builtin = false): int
    {
        $max = (int) $this->db->query('SELECT MAX(sort_order) FROM slides')->fetchColumn();
        $this->db->prepare('INSERT INTO slides (image, alt, position, active, builtin, sort_order) VALUES (?, ?, ?, 1, ?, ?)')
            ->execute([$path, $alt, $position, (int) $builtin, $max + 10]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $alt, string $position): void
    {
        if (!array_key_exists($position, self::POSITIONS)) {
            $position = 'center';
        }
        $this->db->prepare('UPDATE slides SET alt = ?, position = ? WHERE id = ?')->execute([$alt, $position, $id]);
    }

    public function toggle(int $id): ?bool
    {
        $this->db->prepare('UPDATE slides SET active = 1 - active WHERE id = ?')->execute([$id]);
        $s = $this->find($id);
        return $s ? (bool) $s['active'] : null;
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM slides WHERE id = ?')->execute([$id]);
    }

    public function reorder(array $ids): void
    {
        $stmt = $this->db->prepare('UPDATE slides SET sort_order = ? WHERE id = ?');
        $this->db->beginTransaction();
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([($i + 1) * 10, (int) $id]);
        }
        $this->db->commit();
    }
}
