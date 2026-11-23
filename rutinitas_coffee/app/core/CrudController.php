<?php
// Controller CRUD generik. Subclass cukup mendefinisikan model(), fields(), dan hak akses (polymorphism)
abstract class CrudController extends Controller
{
    protected string $slug;
    protected string $title;
    protected array $readRoles  = ['karyawan', 'manajer', 'owner'];
    protected array $writeRoles = ['manajer', 'owner'];

    abstract protected function model(): Model;
    abstract protected function fields(): array;

    public function __construct() { $this->guard($this->readRoles); }

    protected function prepare(array $data, ?int $id): array { return $data; }

    // Field yang dipakai di form tambah/ubah (bisa berbeda dari kolom tabel daftar)
    protected function formFields(?int $id): array { return $this->fields(); }

    // Dipanggil setelah data baru tersimpan
    protected function created(int $newId, array $data): void {}

    protected function rules(?int $id): array
    {
        $r = [];
        foreach ($this->formFields($id) as $n => $f) $r[$n] = $f['rules'] ?? '';
        return $r;
    }

    public function index(): void
    {
        $this->view('crud/index', [
            'title' => $this->title, 'slug' => $this->slug, 'fields' => $this->fields(),
            'rows' => $this->model()->all(), 'pk' => $this->model()->pk(),
            'canWrite' => Auth::can($this->writeRoles),
        ]);
    }

    public function create(): void { $this->guard($this->writeRoles); $this->form(null, []); }

    public function store(): void { $this->guard($this->writeRoles); $this->csrf(); $this->save(null); }

    public function edit($id = null): void
    {
        $this->guard($this->writeRoles);
        $row = $this->model()->find((int)$id);
        if (!$row) $this->notFound();
        $this->form((int)$id, $row);
    }

    public function update($id = null): void
    {
        $this->guard($this->writeRoles); $this->csrf();
        if (!$this->model()->find((int)$id)) $this->notFound();
        $this->save((int)$id);
    }

    public function destroy($id = null): void
    {
        $this->guard($this->writeRoles); $this->csrf();
        try {
            $this->model()->delete((int)$id);
            flash('success', 'Data berhasil dihapus.');
        } catch (PDOException $e) {
            flash('error', 'Data tidak bisa dihapus karena masih dipakai data lain.');
        }
        $this->redirect($this->slug);
    }

    private function save(?int $id): void
    {
        $data = []; $labels = [];
        foreach ($this->formFields($id) as $n => $f) {
            $raw = (string)($_POST[$n] ?? '');
            $data[$n] = ($f['type'] ?? '') === 'password' ? $raw : trim($raw);
            $labels[$n] = $f['label'];
        }
        $v = Validator::make($data, $this->rules($id), $labels, $id);
        if ($v->fails()) { $this->form($id, $data, $v->errors()); return; }

        $data = $this->prepare($data, $id);
        if ($id === null) { $newId = $this->model()->create($data); $this->created($newId, $data); flash('success', 'Data berhasil ditambahkan.'); }
        else { $this->model()->update($id, $data); flash('success', 'Data berhasil diubah.'); }
        $this->redirect($this->slug);
    }

    private function form(?int $id, array $row, array $errors = []): void
    {
        $this->view('crud/form', [
            'title' => $this->title, 'slug' => $this->slug, 'fields' => $this->formFields($id),
            'id' => $id, 'row' => $row, 'errors' => $errors,
        ]);
    }
}
