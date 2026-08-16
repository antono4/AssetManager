<?php
// ============================================================================
//  MODEL: Category
// ============================================================================

class Category
{
    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT c.*, COUNT(a.id) AS asset_count
             FROM categories c LEFT JOIN assets a ON a.category_id = c.id
             GROUP BY c.id, c.name, c.description, c.created_at
             ORDER BY c.name"
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch("SELECT * FROM categories WHERE id=?", [$id]);
    }

    public static function options(): array
    {
        return Database::fetchAll("SELECT id, name FROM categories ORDER BY name");
    }

    public static function create(string $name, string $description = ''): int
    {
        Database::query("INSERT INTO categories (name, description) VALUES (?, ?)", [$name, $description]);
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, string $name, string $description = ''): void
    {
        Database::query("UPDATE categories SET name=?, description=? WHERE id=?", [$name, $description, $id]);
    }

    public static function delete(int $id): bool
    {
        $count = (int)Database::scalar("SELECT COUNT(*) FROM assets WHERE category_id=?", [$id]);
        if ($count > 0) {
            return false; // tidak bisa hapus kategori yang masih dipakai aset
        }
        Database::query("DELETE FROM categories WHERE id=?", [$id]);
        return true;
    }
}
