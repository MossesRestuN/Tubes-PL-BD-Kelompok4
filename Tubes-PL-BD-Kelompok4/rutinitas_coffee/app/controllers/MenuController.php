<?php
class MenuController extends CrudController
{
    protected string $slug = 'menu';
    protected string $title = 'Menu';
    protected array $writeRoles = ['manajer', 'owner'];

    protected function model(): Model { return new Menu(); }

    protected function fields(): array
    {
        return [
            'nama_menu' => ['label' => 'Nama Menu', 'type' => 'text', 'rules' => 'required|max:50'],
            'kategori'  => ['label' => 'Kategori', 'type' => 'select', 'options' => ['Coffee', 'Non-Coffee', 'Makanan'], 'rules' => 'required|in:Coffee,Non-Coffee,Makanan'],
            'harga'     => ['label' => 'Harga (Rp)', 'type' => 'number', 'format' => 'rupiah', 'rules' => 'required|numeric|min:0'],
            'status_ketersediaan' => ['label' => 'Status', 'type' => 'select', 'options' => ['Tersedia', 'Kosong'], 'default' => 'Tersedia', 'format' => 'status', 'rules' => 'required|in:Tersedia,Kosong'],
        ];
    }
}
