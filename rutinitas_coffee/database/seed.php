<?php
// Jalankan SEKALI setelah rutinitas_coffee.sql: php database/seed.php
define('ROOT', dirname(__DIR__));
require ROOT . '/app/core/Database.php';

$users = [
    ['Owner Rutinitas', 'owner',    'owner123',    'owner'],
    ['Manajer Shift',   'manajer',  'manajer123',  'manajer'],
    ['Karyawan Kasir',  'karyawan', 'karyawan123', 'karyawan'],
];
$stmt = Database::connect()->prepare(
    'INSERT INTO pengguna (nama, username, password, role) VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)'
);
foreach ($users as [$nama, $user, $pass, $role]) {
    $stmt->execute([$nama, $user, password_hash($pass, PASSWORD_DEFAULT), $role]);
    echo "Akun $role dibuat: $user / $pass\n";
}
echo "Selesai. Segera ganti password lewat menu Pengguna.\n";
