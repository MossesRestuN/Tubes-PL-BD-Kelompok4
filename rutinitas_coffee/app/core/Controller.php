<?php
abstract class Controller
{
    // Wajib login; jika $roles diisi, peran harus termasuk di dalamnya
    protected function guard(array $roles = []): void
    {
        if (!Auth::check()) $this->redirect('auth/login');
        if ($roles && !Auth::can($roles)) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }

    protected function redirect(string $path): void { header('Location: ' . url($path)); exit; }

    protected function csrf(): void
    {
        $ok = $_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($_SESSION['csrf'] ?? '', $_POST['_token'] ?? '');
        if (!$ok) { http_response_code(419); exit('Token keamanan tidak valid. Muat ulang halaman.'); }
    }

    protected function notFound(): void { http_response_code(404); $this->view('errors/404'); exit; }

    // Header dan footer dijalankan di scope terpisah supaya variabel milik layout
    // (misalnya $slug pada menu samping) tidak menimpa variabel milik halaman.
    protected function view(string $file, array $data = []): void
    {
        (static function (): void { require ROOT . '/app/views/layout/header.php'; })();
        (static function () use ($file, $data): void {
            extract($data, EXTR_SKIP);
            require ROOT . "/app/views/$file.php";
        })();
        (static function (): void { require ROOT . '/app/views/layout/footer.php'; })();
    }
}
