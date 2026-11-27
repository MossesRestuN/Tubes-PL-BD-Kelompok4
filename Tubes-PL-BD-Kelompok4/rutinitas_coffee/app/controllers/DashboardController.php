<?php
class DashboardController extends Controller
{
    public function __construct() { $this->guard(); }

    public function index(): void
    {
        $role = Auth::role();
        $p = new Pesanan();
        // Karyawan hanya melihat angka miliknya; manajer dan owner melihat seluruh kedai
        $data = ['role' => $role, 'r' => $p->ringkasan($role === 'karyawan' ? Auth::id() : null)];
        if (in_array($role, ['manajer', 'owner'], true)) {
            $data['perBulan'] = $p->perBulan((int)date('Y'));
            $data['perTahun'] = $p->perTahun();
            $data['stokMenipis'] = (new Bahan())->menipis();
        }
        if ($role === 'owner') $data['jumlahUser'] = (new Pengguna())->count();
        $this->view('dashboard/index', $data);
    }
}