<?php
// Kelas induk (abstraction + inheritance) untuk semua Model
abstract class Model
{
    protected string $table;
    protected string $pk;
    protected array $fillable = [];

    protected function qb(): QueryBuilder { return QueryBuilder::table($this->table); }
    protected function only(array $d): array { return array_intersect_key($d, array_flip($this->fillable)); }

    public function pk(): string { return $this->pk; }
    public function all(): array { return $this->qb()->orderBy($this->pk, 'DESC')->get(); }
    public function find(int $id): ?array { return $this->qb()->where($this->pk, '=', $id)->first(); }
    public function count(): int { return $this->qb()->count(); }
    public function create(array $d): int { return $this->qb()->insert($this->only($d)); }
    public function update(int $id, array $d): void { $this->qb()->where($this->pk, '=', $id)->update($this->only($d)); }
    public function delete(int $id): void { $this->qb()->where($this->pk, '=', $id)->delete(); }
}
