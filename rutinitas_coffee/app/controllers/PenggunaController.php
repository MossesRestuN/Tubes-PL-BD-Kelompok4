<?php
// Hanya owner yang boleh membuka dan mengelola akun pengguna
class PenggunaController extends CrudController
{
    protected string $slug = 'pengguna';
    protected string $title = 'Pengguna';
    protected array $readRoles = ['owner'];
    protected array $writeRoles = ['owner'];

    protected function model(): Model { return new Pengguna(); }

    protected function fields(): array
    {
        return [
            'nama'     => ['label' => 'Nama', 'type' => 'text', 'rules' => 'required|max:50'],
            'username' => ['label' => 'Username', 'type' => 'text', 'rules' => 'required|max:30|unique:pengguna,username,id_pengguna'],
            'role'     => ['label' => 'Peran', 'type' => 'select', 'options' => ['karyawan', 'manajer', 'owner'], 'rules' => 'required|in:karyawan,manajer,owner'],
            'password' => ['label' => 'Password', 'type' => 'password', 'rules' => 'required|min:6|max:72', 'help' => 'Saat mengubah data, kosongkan jika password tidak diganti.'],
        ];
    }

    // Password opsional saat edit
    protected function rules(?int $id): array
    {
        $r = parent::rules($id);
        if ($id !== null) $r['password'] = 'min:6|max:72';
        return $r;
    }

    protected function prepare(array $data, ?int $id): array
    {
        if ($data['password'] === '') unset($data['password']);
        else $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $data;
    }

    public function destroy($id = null): void
    {
        $this->guard($this->writeRoles);
        if ((int)$id === Auth::id()) {
            $this->csrf();
            flash('error', 'Akun yang sedang dipakai tidak bisa dihapus.');
            $this->redirect($this->slug);
        }
        parent::destroy($id);
    }
}
