<?php
class Menu extends Model
{
    protected string $table = 'menu';
    protected string $pk = 'id_menu';
    protected array $fillable = ['nama_menu', 'kategori', 'harga'];

    public function all(): array { return $this->qb()->orderBy('nama_menu')->get(); }
}
