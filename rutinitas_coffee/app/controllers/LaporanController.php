<?php
// Manajer: rekap harian dan menu terlaris. Owner: ditambah kinerja per pengguna.
class LaporanController extends Controller
{
    public function __construct() { $this->guard(['manajer', 'owner']); }

    public function index(): void
    {
        $valid = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$d) === 1;
        $dari = $valid($_GET['dari'] ?? '') ? $_GET['dari'] : date('Y-m-01');
        $sampai = $valid($_GET['sampai'] ?? '') ? $_GET['sampai'] : date('Y-m-d');
        $p = new Pesanan();
        $data = ['dari' => $dari, 'sampai' => $sampai, 'harian' => $p->harian($dari, $sampai), 'terlaris' => $p->terlaris($dari, $sampai)];
        if (Auth::role() === 'owner') $data['perKasir'] = $p->perKasir($dari, $sampai);
        $this->view('laporan/index', $data);
    }
}
