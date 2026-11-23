<?php
class Pengguna extends Model
{
    protected string $table = 'pengguna';
    protected string $pk = 'id_pengguna';
    protected array $fillable = ['nama', 'username', 'password', 'role'];

    public function findByUsername(string $u): ?array { return $this->qb()->where('username', '=', $u)->first(); }
}
