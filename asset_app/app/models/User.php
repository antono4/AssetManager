<?php
// ============================================================================
//  MODEL: User
// ============================================================================

class User
{
    public static function all(): array
    {
        return Database::fetchAll("SELECT * FROM users ORDER BY name");
    }

    public static function find(int $id): ?array
    {
        return Database::fetch("SELECT * FROM users WHERE id=?", [$id]);
    }

    public static function findByUsername(string $username): ?array
    {
        return Database::fetch("SELECT * FROM users WHERE username=?", [$username]);
    }

    public static function create(array $d): int
    {
        $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        Database::query(
            "INSERT INTO users (name, username, email, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)",
            [$d['name'], $d['username'], $d['email'] ?: null, $hash, $d['role'], (int)($d['is_active'] ?? 1)]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $d, bool $changePassword = false): void
    {
        if ($changePassword && !empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            Database::query(
                "UPDATE users SET name=?, username=?, email=?, role=?, is_active=?, password=? WHERE id=?",
                [$d['name'], $d['username'], $d['email'] ?: null, $d['role'], (int)$d['is_active'], $hash, $id]
            );
        } else {
            Database::query(
                "UPDATE users SET name=?, username=?, email=?, role=?, is_active=? WHERE id=?",
                [$d['name'], $d['username'], $d['email'] ?: null, $d['role'], (int)$d['is_active'], $id]
            );
        }
    }

    public static function delete(int $id): bool
    {
        if ($id === Auth::id()) {
            return false;
        }
        Database::query("DELETE FROM users WHERE id=?", [$id]);
        return true;
    }
}
