<?php
// Koneksi PDO (singleton)
class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $d = (require ROOT . '/config/config.php')['db'];
            $dsn = "mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}";
            try {
                self::$pdo = new PDO($dsn, $d['user'], $d['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Koneksi database gagal. Periksa config/config.php dan pastikan MySQL aktif.');
            }
        }
        return self::$pdo;
    }

    // Semua query memakai prepared statement
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Jalankan $fn dalam satu transaksi: commit jika sukses, rollback jika ada error
    public static function transaction(callable $fn)
    {
        $pdo = self::connect();
        $pdo->beginTransaction();
        try {
            $hasil = $fn();
            $pdo->commit();
            return $hasil;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
