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
        $photo = self::handlePhotoUpload();
        Database::query(
            "INSERT INTO users (name, username, email, password, role, is_active, photo) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$d['name'], $d['username'], $d['email'] ?: null, $hash, $d['role'], (int)($d['is_active'] ?? 1), $photo]
        );
        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $d, bool $changePassword = false): void
    {
        $old = self::find($id);
        $photo = $old['photo'] ?? null;
        // Upload foto baru bila ada (hapus foto lama)
        if (!empty($_FILES['photo']['name']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            if ($photo) {
                self::deletePhotoFile($photo);
            }
            $photo = self::handlePhotoUpload();
        }
        if ($changePassword && !empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
            Database::query(
                "UPDATE users SET name=?, username=?, email=?, role=?, is_active=?, password=?, photo=? WHERE id=?",
                [$d['name'], $d['username'], $d['email'] ?: null, $d['role'], (int)$d['is_active'], $hash, $photo, $id]
            );
        } else {
            Database::query(
                "UPDATE users SET name=?, username=?, email=?, role=?, is_active=?, photo=? WHERE id=?",
                [$d['name'], $d['username'], $d['email'] ?: null, $d['role'], (int)$d['is_active'], $photo, $id]
            );
        }
    }

    // Hapus foto user (set photo=NULL, hapus file di disk)
    public static function removePhoto(int $id): void
    {
        $user = self::find($id);
        if ($user && !empty($user['photo'])) {
            self::deletePhotoFile($user['photo']);
            Database::query("UPDATE users SET photo=NULL WHERE id=?", [$id]);
        }
    }

    // Upload file foto user, return path relatif atau null
    public static function handlePhotoUpload(): ?string
    {
        if (empty($_FILES['photo']['name']) || ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowed, true)) {
            return null;
        }
        if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
            return null;
        }
        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'img',
        };
        $name = 'user_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = PUBLIC_PATH . '/uploads/users/' . $name;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            return 'uploads/users/' . $name;
        }
        return null;
    }

    // Hapus file foto user dari disk
    public static function deletePhotoFile(?string $path): void
    {
        if (!$path) {
            return;
        }
        $full = PUBLIC_PATH . '/' . $path;
        if (str_starts_with(realpath($full) ?: '', PUBLIC_PATH . '/uploads/users') && is_file($full)) {
            @unlink($full);
        }
    }

    public static function delete(int $id): bool
    {
        if ($id === Auth::id()) {
            return false;
        }
        self::removePhoto($id);
        Database::query("DELETE FROM users WHERE id=?", [$id]);
        return true;
    }
}
