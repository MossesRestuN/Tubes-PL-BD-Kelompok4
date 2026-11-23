<?php
class PesananController extends Controller
{
    private Pesanan $m;

    public function __construct() { $this->guard(); $this->m = new Pesanan(); }

    public function index(): void
    {
        // Karyawan hanya melihat pesanan miliknya; manajer dan owner melihat semua
        $uid = Auth::role() === 'karyawan' ? Auth::id() : null;
        $this->view('pesanan/index', ['rows' => $this->m->daftar($uid)]);
    }

    public function create(): void { $this->formView([], []); }

    public function store(): void
    {
        $this->csrf();
        $data = [];
        foreach (['mode_layanan', 'metode_pembayaran', 'nama_pelanggan', 'catatan'] as $k) $data[$k] = trim((string)($_POST[$k] ?? ''));

        $v = Validator::make($data, [
            'mode_layanan'      => 'required|in:Dine In,Take Away',
            'metode_pembayaran' => 'required|in:Cash,QRIS',
            'nama_pelanggan'    => 'max:50',
            'catatan'           => 'max:255',
        ], ['mode_layanan' => 'Mode layanan', 'metode_pembayaran' => 'Metode pembayaran', 'nama_pelanggan' => 'Nama pelanggan', 'catatan' => 'Catatan']);

        $items = [];
        foreach ((array)($_POST['id_menu'] ?? []) as $i => $idMenu) {
            $qty = (int)($_POST['jumlah'][$i] ?? 0);
            if ((int)$idMenu > 0 && $qty > 0) $items[(int)$idMenu] = ($items[(int)$idMenu] ?? 0) + $qty;
        }
        $errors = $v->errors();
        if (!$items) $errors['items'] = 'Pilih minimal satu menu dengan jumlah yang valid.';
        if ($errors) { $this->formView($errors, $data); return; }

        try {
            $id = $this->m->buat($data + ['id_pengguna' => Auth::id()], $items);
        } catch (Throwable $e) {
            $this->formView(['items' => 'Pesanan gagal disimpan. Coba lagi.'], $data);
            return;
        }
        flash('success', 'Pesanan berhasil dicatat.');
        $this->redirect('pesanan/show/' . $id);
    }

    public function show($id = null): void
    {
        $d = $this->m->detail((int)$id);
        if (!$d) $this->notFound();
        if (Auth::role() === 'karyawan' && (int)$d['header']['id_pengguna'] !== Auth::id()) $this->guard(['manajer', 'owner']);
        $this->view('pesanan/show', $d);
    }

    // Pembatalan pesanan hanya untuk manajer dan owner
    public function destroy($id = null): void
    {
        $this->guard(['manajer', 'owner']);
        $this->csrf();
        $this->m->delete((int)$id);
        flash('success', 'Pesanan dibatalkan.');
        $this->redirect('pesanan');
    }

    private function formView(array $errors, array $old): void
    {
        $this->view('pesanan/create', ['menus' => (new Menu())->all(), 'errors' => $errors, 'old' => $old]);
    }
}
