<?php
// Bahan baku: CRUD data bahan (manajer dan owner), pengambilan dan penambahan stok (semua peran).
// Mitra tidak menghitung selisih stok secara spesifik dan tidak memiliki resep, jadi stok tidak
// dikurangi otomatis dari penjualan. Stok hanya berubah lewat pencatatan ambil dan tambah.
class BahanController extends CrudController
{
    protected string $slug = 'bahan';
    protected string $title = 'Bahan Baku';
    protected array $writeRoles = ['manajer', 'owner'];

    public function __construct()
    {
        parent::__construct();
        (new Bahan())->pastikanTabel();
    }

    protected function model(): Model { return new Bahan(); }

    protected function fields(): array
    {
        return [
            'nama_bahan'   => ['label' => 'Nama Bahan', 'type' => 'text', 'rules' => 'required|max:50'],
            'kategori'     => ['label' => 'Kategori', 'type' => 'text', 'rules' => 'max:30'],
            'stok'         => ['label' => 'Stok', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
            'stok_minimum' => ['label' => 'Stok Minimum', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
            'satuan'       => ['label' => 'Satuan', 'type' => 'text', 'rules' => 'required|max:15'],
            'vendor'       => ['label' => 'Vendor', 'type' => 'text', 'rules' => 'max:50'],
        ];
    }

    // Saat mengubah data, stok tidak bisa diedit langsung agar semua perubahan tercatat di riwayat
    protected function formFields(?int $id): array
    {
        $f = $this->fields();
        if ($id !== null) unset($f['stok']);
        return $f;
    }

    protected function created(int $newId, array $data): void
    {
        if ((float)$data['stok'] > 0) (new Bahan())->catatAwal($newId, (int)Auth::id(), (float)$data['stok']);
    }

    public function index(): void
    {
        $m = new Bahan();
        $filter = $_GET['jenis'] ?? 'semua';
        $jenis = match ($filter) { 'ambil' => ['ambil'], 'tambah' => ['tambah', 'awal'], default => [] };
        if (!in_array($filter, ['ambil', 'tambah'], true)) $filter = 'semua';
        $this->view('bahan/index', [
            'rows' => $m->all(), 'canWrite' => Auth::can($this->writeRoles),
            'riwayat' => $m->riwayat(null, 15, $jenis), 'filter' => $filter,
        ]);
    }

    public function ambil($id = null): void { $this->gerak('ambil', (int)$id); }
    public function tambah($id = null): void { $this->gerak('tambah', (int)$id); }

    public function riwayat($id = null): void
    {
        $m = new Bahan();
        $bahan = null;
        if ($id !== null && $id !== '') {
            $bahan = $m->find((int)$id);
            if (!$bahan) $this->notFound();
        }
        $this->view('bahan/riwayat', ['bahan' => $bahan, 'rows' => $m->riwayat($bahan ? (int)$bahan['id_bahan'] : null)]);
    }

    private function gerak(string $jenis, int $id): void
    {
        $m = new Bahan();
        $bahan = $m->find($id);
        if (!$bahan) $this->notFound();

        $errors = [];
        $old = ['jumlah' => '1', 'keterangan' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->csrf();
            $old = ['jumlah' => trim((string)($_POST['jumlah'] ?? '')), 'keterangan' => trim((string)($_POST['keterangan'] ?? ''))];
            $v = Validator::make($old,
                ['jumlah' => 'required|numeric|min:0.01', 'keterangan' => 'max:255'],
                ['jumlah' => 'Jumlah', 'keterangan' => 'Keterangan']);
            $errors = $v->errors();
            if (!$errors) {
                try {
                    $m->catatGerak($id, (int)Auth::id(), $jenis, (float)$old['jumlah'], $old['keterangan']);
                    flash('success', $jenis === 'ambil' ? 'Pengambilan stok dicatat.' : 'Penambahan stok dicatat.');
                    $this->redirect('bahan');
                } catch (RuntimeException $e) {
                    $errors['jumlah'] = $e->getMessage();
                } catch (Throwable $e) {
                    $errors['jumlah'] = 'Gagal menyimpan: ' . $e->getMessage();
                }
            }
        }
        $this->view('bahan/gerak', ['bahan' => $bahan, 'jenis' => $jenis, 'old' => $old, 'errors' => $errors]);
    }
}
