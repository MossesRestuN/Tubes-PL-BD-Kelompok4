<?php
// Bahan baku: CRUD data bahan (manajer dan owner), pengambilan dan penambahan stok (semua peran).
// Mitra tidak menghitung selisih stok secara spesifik dan tidak memiliki resep, jadi stok tidak
// dikurangi otomatis dari penjualan. Stok hanya berubah lewat pencatatan ambil dan tambah.
// Setiap stok masuk menjadi satu batch (disimpan di riwayat_stok) dengan tanggal kadaluarsa sendiri.
class BahanController extends CrudController
{
    protected string $slug = 'bahan';
    protected string $title = 'Bahan Baku';
    protected array $writeRoles = ['manajer', 'owner'];

    protected function model(): Model { return new Bahan(); }

    protected function fields(): array
    {
        return [
            'nama_bahan'   => ['label' => 'Nama Bahan', 'type' => 'text', 'rules' => 'required|max:50'],
            'kategori'     => ['label' => 'Kategori', 'type' => 'text', 'rules' => 'max:30'],
            'stok'         => ['label' => 'Stok', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
            'tanggal_kadaluarsa' => ['label' => 'Tanggal Kadaluarsa Stok Awal (opsional)', 'type' => 'date', 'rules' => 'date', 'help' => 'Stok berikutnya dicatat lewat tombol Tambah, lengkap dengan tanggal kadaluarsanya.'],
            'stok_minimum' => ['label' => 'Stok Minimum', 'type' => 'number', 'rules' => 'required|numeric|min:0'],
            'satuan'       => ['label' => 'Satuan', 'type' => 'text', 'rules' => 'required|max:15'],
            'vendor'       => ['label' => 'Vendor', 'type' => 'text', 'rules' => 'max:50'],
        ];
    }

    // Saat mengubah data, stok tidak bisa diedit langsung agar semua perubahan tercatat di riwayat
    protected function formFields(?int $id): array
    {
        $f = $this->fields();
        if ($id !== null) unset($f['stok'], $f['tanggal_kadaluarsa']);
        return $f;
    }

    protected function created(int $newId, array $data): void
    {
        if ((float)$data['stok'] > 0) (new Bahan())->catatAwal($newId, (int)Auth::id(), (float)$data['stok'], ($data['tanggal_kadaluarsa'] ?? '') !== '' ? $data['tanggal_kadaluarsa'] : null);
    }

    public function index(): void
    {
        $m = new Bahan();
        $filter = $_GET['jenis'] ?? 'semua';
        $jenis = match ($filter) { 'ambil' => ['ambil'], 'tambah' => ['tambah', 'awal'], default => [] };
        if (!in_array($filter, ['ambil', 'tambah'], true)) $filter = 'semua';
        $this->view('bahan/index', [
            'rows' => $m->all(), 'canWrite' => Auth::can($this->writeRoles),
            'riwayat' => $m->riwayat(null, 3, $jenis), 'filter' => $filter,
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

    // Dikirim dari jendela Ambil/Tambah di daftar bahan. Hasilnya (berhasil atau gagal) tampil lewat pesan flash.
    private function gerak(string $jenis, int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('bahan');
        $this->csrf();
        $bahan = (new Bahan())->find($id);
        if (!$bahan) $this->notFound();

        $in = [];
        foreach (['jumlah', 'keterangan', 'tanggal', 'kadaluarsa'] as $k) $in[$k] = trim((string)($_POST[$k] ?? ''));
        if ($jenis === 'ambil') $in['kadaluarsa'] = '';

        $rules = ['jumlah' => 'required|numeric|min:0.01', 'keterangan' => 'max:255', 'kadaluarsa' => 'date'];
        $errors = Validator::make($in, $rules, ['jumlah' => 'Jumlah', 'keterangan' => 'Keterangan', 'kadaluarsa' => 'Tanggal kadaluarsa'])->errors();

        // Tanggal dan jam kejadian dari input datetime-local (Y-m-dTH:i), tidak boleh di masa depan
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $in['tanggal']);
        if (!$dt || $dt->format('Y-m-d\TH:i') !== $in['tanggal']) $errors['tanggal'] = 'Tanggal dan jam tidak valid.';
        elseif ($dt > new DateTime('+1 minute')) $errors['tanggal'] = 'Tanggal dan jam tidak boleh lebih dari sekarang.';
        elseif ($in['kadaluarsa'] !== '' && $in['kadaluarsa'] < $dt->format('Y-m-d')) $errors['kadaluarsa'] = 'Tanggal kadaluarsa tidak boleh sebelum tanggal masuk.';

        if ($errors) {
            flash('error', implode(' ', $errors));
        } else {
            try {
                $dibuang = (new Bahan())->catatGerak($id, (int)Auth::id(), $jenis, (float)$in['jumlah'], $in['keterangan'],
                    $dt->format('Y-m-d H:i:00'), $in['kadaluarsa'] !== '' ? $in['kadaluarsa'] : null);
                $msg = $jenis === 'ambil' ? 'Pengambilan stok dicatat.' : 'Penambahan stok dicatat.';
                if ($dibuang > 0) $msg .= ' Stok lama yang sudah kadaluarsa (' . ($dibuang + 0) . ' ' . $bahan['satuan'] . ') dibuang otomatis.';
                flash('success', $msg);
            } catch (RuntimeException $e) {
                flash('error', $e->getMessage());
            } catch (Throwable) {
                flash('error', 'Gagal menyimpan. Coba lagi.');
            }
        }
        $this->redirect('bahan');
    }
}
