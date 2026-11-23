<?php
// Autentikasi berbasis sesi dan pemeriksaan peran
class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $user = (new Pengguna())->findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)$user['id_pengguna'], 'nama' => $user['nama'], 'role' => $user['role']];
            return true;
        }
        return false;
    }

    public static function check(): bool { return isset($_SESSION['user']); }
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function id(): ?int { return $_SESSION['user']['id'] ?? null; }
    public static function role(): ?string { return $_SESSION['user']['role'] ?? null; }
    public static function can(array $roles): bool { return self::check() && in_array(self::role(), $roles, true); }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
