<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Auth
{
    public function __construct(private readonly PDO $db, private readonly array $config) {}

    public function hasUsers(): bool
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0;
    }

    public function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        $stmt = $this->db->prepare('SELECT id, username FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch() ?: null;
    }

    public function createUser(string $username, string $password): int
    {
        $this->db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        return (int) $this->db->lastInsertId();
    }

    public function isLockedOut(string $ip): bool
    {
        $since = time() - $this->config['login_window_minutes'] * 60;
        $this->db->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')->execute([$since]);
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at >= ?');
        $stmt->execute([$ip, $since]);
        return (int) $stmt->fetchColumn() >= $this->config['login_max_attempts'];
    }

    public function attempt(string $username, string $password, string $ip): bool
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->db->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)')->execute([$ip, time()]);
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $this->changePassword((int) $user['id'], $password);
        }
        $this->db->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
        $this->loginById((int) $user['id']);
        return true;
    }

    public function loginById(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
    }

    public function verifyPassword(int $userId, string $password): bool
    {
        $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();
        return $hash !== false && password_verify($password, $hash);
    }

    public function changePassword(int $userId, string $password): void
    {
        $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
