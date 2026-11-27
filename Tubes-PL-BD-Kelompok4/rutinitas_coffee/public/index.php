<?php
// Front controller: semua request masuk lewat sini (index.php?url=controller/action/param)
session_start();
date_default_timezone_set('Asia/Makassar'); // WITA, sama dengan jam perangkat di Balikpapan
define('ROOT', dirname(__DIR__));
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

spl_autoload_register(function (string $class): void {
    foreach (['core', 'models', 'controllers'] as $dir) {
        $file = ROOT . "/app/$dir/$class.php";
        if (is_file($file)) { require $file; return; }
    }
});
require ROOT . '/app/core/helpers.php';

$parts  = explode('/', trim($_GET['url'] ?? 'dashboard/index', '/'));
$name   = preg_replace('/[^a-z]/i', '', $parts[0]) ?: 'dashboard';
$class  = ucfirst(strtolower($name)) . 'Controller';
$action = preg_replace('/[^a-z]/i', '', $parts[1] ?? 'index') ?: 'index';
$params = array_slice($parts, 2);

if (class_exists($class) && is_subclass_of($class, 'Controller') && (new ReflectionClass($class))->isInstantiable()) {
    $controller = new $class();
    if (is_callable([$controller, $action])) {
        call_user_func_array([$controller, $action], $params);
        exit;
    }
}
http_response_code(404);
echo 'Halaman tidak ditemukan.';
