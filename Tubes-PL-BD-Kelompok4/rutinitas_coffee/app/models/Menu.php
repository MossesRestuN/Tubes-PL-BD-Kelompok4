<?php
class Menu extends Model
{
    protected string $table = 'menu';
    protected string $pk = 'id_menu';
    protected array $fillable = ['nama_menu', 'kategori', 'harga', 'status_ketersediaan'];

    public function all(): array { return $this->qb()->orderBy('nama_menu')->get(); }

    // Hanya menu berstatus Tersedia yang bisa dipesan
    public function tersedia(): array { return $this->qb()->where('status_ketersediaan', '=', 'Tersedia')->orderBy('nama_menu')->get(); }
}
