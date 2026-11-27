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
            $this->formView(['items' => $e instanceof RuntimeException ? $e->getMessage() : 'Pesanan gagal disimpan. Coba lagi.'], $data);
            return;
        }
        flash('success', 'Pesanan berhasil dicatat.');
        $this->redirect('pesanan&detail=' . $id); // daftar pesanan langsung membuka rincian pesanan ini
    }

    // Rincian pesanan untuk jendela mengambang di daftar pesanan (JSON, tanpa memuat halaman baru)
    public function rincian($id = null): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $d = $this->m->detail((int)$id);
        if (!$d) { http_response_code(404); exit(json_encode(['error' => 'Pesanan tidak ditemukan.'])); }
        $h = $d['header'];
        if (Auth::role() === 'karyawan' && (int)$h['id_pengguna'] !== Auth::id()) {
            http_response_code(403);
            exit(json_encode(['error' => 'Anda tidak boleh melihat pesanan ini.']));
        }
        echo json_encode([
            'id' => (int)$h['id_pesanan'], 'tanggal' => $h['tanggal'], 'kasir' => $h['kasir'],
            'pelanggan' => $h['nama_pelanggan'] ?: 'Umum', 'mode' => $h['mode_layanan'], 'bayar' => $h['metode_pembayaran'],
            'catatan' => (string)($h['catatan'] ?? ''), 'total' => rupiah($h['total']),
            'items' => array_map(fn($i) => [
                'menu' => $i['nama_menu'], 'harga' => rupiah($i['harga_satuan']),
                'jumlah' => (int)$i['jumlah'], 'subtotal' => rupiah($i['subtotal']),
            ], $d['items']),
        ], JSON_UNESCAPED_UNICODE);
        exit;
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
        $this->view('pesanan/create', ['menus' => (new Menu())->tersedia(), 'errors' => $errors, 'old' => $old]);
    }
}
