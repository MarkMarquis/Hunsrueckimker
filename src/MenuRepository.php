<?php
declare(strict_types=1);

namespace App;

use PDO;

final class MenuRepository
{
    public function __construct(private readonly PDO $db) {}

    public const SECTIONS = [
        'highlight' => 'Highlight-Bereich',
        'menu'   => 'Honigsorten',
    ];

    /**
     * Kategorien inkl. Sorten.
     * $onlyActive = nur sichtbare Sorten (Frontend), $section = nur ein Bereich.
     */
    public function tree(bool $onlyActive = true, ?string $section = null): array
    {
        $cats = $this->categories();
        if ($section !== null) {
            $cats = array_values(array_filter($cats, fn ($c) => $c['section'] === $section));
        }
        $sql = 'SELECT * FROM menu_items' . ($onlyActive ? ' WHERE active = 1' : '') . ' ORDER BY sort_order, name';
        $byCat = [];
        foreach ($this->db->query($sql) as $item) {
            $item['tag_list'] = array_values(array_filter(array_map('trim', explode(',', $item['tags']))));
            $item['variant_list'] = self::decodeVariants($item['variants'] ?? '[]');
            $byCat[$item['category_id']][] = $item;
        }
        foreach ($cats as &$cat) {
            $cat['items'] = $byCat[$cat['id']] ?? [];
        }
        unset($cat);
        if ($onlyActive) {
            $cats = array_values(array_filter($cats, fn ($c) => $c['items'] !== []));
        }
        return $cats;
    }

    public static function decodeVariants(?string $json): array
    {
        $list = json_decode((string) $json, true);
        return is_array($list) ? array_values(array_filter($list, fn ($v) => isset($v['label'], $v['price_cents']))) : [];
    }

    public function categories(): array
    {
        return $this->db->query('SELECT * FROM menu_categories ORDER BY sort_order, name')->fetchAll();
    }

    public function findCategory(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM menu_categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function saveCategory(array $d, ?int $id = null): int
    {
        if ($id) {
            $this->db->prepare('UPDATE menu_categories SET name = ?, description = ?, section = ?, sort_order = ? WHERE id = ?')
                ->execute([$d['name'], $d['description'], $d['section'], $d['sort_order'], $id]);
            return $id;
        }
        $this->db->prepare('INSERT INTO menu_categories (name, description, section, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$d['name'], $d['description'], $d['section'], $d['sort_order']]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteCategory(int $id): void
    {
        $this->db->prepare('DELETE FROM menu_categories WHERE id = ?')->execute([$id]);
    }

    public function findItem(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM menu_items WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function saveItem(array $d, ?int $id = null): int
    {
        $params = [
            $d['category_id'], $d['name'], $d['description'], $d['price_cents'], $d['portion'],
            json_encode($d['variants'], JSON_UNESCAPED_UNICODE), $d['allergens'], $d['tags'], $d['active'], $d['sort_order'],
            $d['image'] ?? '',
        ];
        if ($id) {
            $params[] = $id;
            $this->db->prepare(
                'UPDATE menu_items SET category_id = ?, name = ?, description = ?, price_cents = ?, portion = ?,
                 variants = ?, allergens = ?, tags = ?, active = ?, sort_order = ?, image = ? WHERE id = ?'
            )->execute($params);
            return $id;
        }
        $this->db->prepare(
            'INSERT INTO menu_items (category_id, name, description, price_cents, portion, variants, allergens, tags, active, sort_order, image)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute($params);
        return (int) $this->db->lastInsertId();
    }

    public function deleteItem(int $id): void
    {
        $this->db->prepare('DELETE FROM menu_items WHERE id = ?')->execute([$id]);
    }

    public function toggleItem(int $id): ?bool
    {
        $this->db->prepare('UPDATE menu_items SET active = 1 - active WHERE id = ?')->execute([$id]);
        $item = $this->findItem($id);
        return $item ? (bool) $item['active'] : null;
    }

    /** Neue Reihenfolge aus Drag & Drop speichern. */
    public function reorder(string $table, array $ids): void
    {
        $table = $table === 'categories' ? 'menu_categories' : 'menu_items';
        $stmt = $this->db->prepare("UPDATE {$table} SET sort_order = ? WHERE id = ?");
        $this->db->beginTransaction();
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([($i + 1) * 10, (int) $id]);
        }
        $this->db->commit();
    }

    public function nextSort(string $table, ?int $categoryId = null): int
    {
        if ($table === 'categories') {
            $max = $this->db->query('SELECT MAX(sort_order) FROM menu_categories')->fetchColumn();
        } else {
            $stmt = $this->db->prepare('SELECT MAX(sort_order) FROM menu_items WHERE category_id = ?');
            $stmt->execute([$categoryId]);
            $max = $stmt->fetchColumn();
        }
        return ((int) $max) + 10;
    }
}
