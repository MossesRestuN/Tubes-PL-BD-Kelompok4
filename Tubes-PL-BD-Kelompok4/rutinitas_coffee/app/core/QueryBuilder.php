<?php
// Query Builder sederhana: nama kolom divalidasi, nilai selalu di-bind
class QueryBuilder
{
    private string $cols = '*';
    private array $wheres = [];
    private array $binds = [];
    private string $order = '';
    private ?int $limit = null;
    private int $offset = 0;

    private function __construct(private string $table) {}

    public static function table(string $t): self { return new self(self::ident($t)); }

    private static function ident(string $n): string
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $n)) throw new InvalidArgumentException('Nama kolom/tabel tidak valid');
        return $n;
    }

    public function select(array $cols): self { $this->cols = implode(', ', array_map([self::class, 'ident'], $cols)); return $this; }

    public function where(string $col, string $op, $val): self
    {
        if (!in_array($op, ['=', '!=', '<', '>', '<=', '>=', 'LIKE'], true)) throw new InvalidArgumentException('Operator tidak valid');
        $key = ':w' . count($this->binds);
        $this->wheres[] = self::ident($col) . " $op $key";
        $this->binds[$key] = $val;
        return $this;
    }

    public function orderBy(string $col, string $dir = 'ASC'): self
    {
        $this->order = ' ORDER BY ' . self::ident($col) . (strtoupper($dir) === 'DESC' ? ' DESC' : ' ASC');
        return $this;
    }

    public function limit(int $n, int $offset = 0): self { $this->limit = $n; $this->offset = $offset; return $this; }

    private function whereSql(): string { return $this->wheres ? ' WHERE ' . implode(' AND ', $this->wheres) : ''; }

    public function get(): array
    {
        $sql = "SELECT {$this->cols} FROM {$this->table}" . $this->whereSql() . $this->order;
        if ($this->limit !== null) $sql .= " LIMIT {$this->limit} OFFSET {$this->offset}";
        return Database::query($sql, $this->binds)->fetchAll();
    }

    public function first(): ?array { $rows = $this->limit(1)->get(); return $rows[0] ?? null; }

    public function count(): int
    {
        return (int) Database::query("SELECT COUNT(*) FROM {$this->table}" . $this->whereSql(), $this->binds)->fetchColumn();
    }

    public function insert(array $data): int
    {
        $cols = $ph = $b = []; $i = 0;
        foreach ($data as $c => $v) { $cols[] = self::ident($c); $ph[] = ":i$i"; $b[":i$i"] = $v; $i++; }
        Database::query("INSERT INTO {$this->table} (" . implode(', ', $cols) . ') VALUES (' . implode(', ', $ph) . ')', $b);
        return (int) Database::connect()->lastInsertId();
    }

    public function update(array $data): int
    {
        if (!$this->wheres) throw new LogicException('UPDATE tanpa WHERE ditolak');
        $set = []; $b = $this->binds; $i = 0;
        foreach ($data as $c => $v) { $set[] = self::ident($c) . " = :s$i"; $b[":s$i"] = $v; $i++; }
        return Database::query("UPDATE {$this->table} SET " . implode(', ', $set) . $this->whereSql(), $b)->rowCount();
    }

    public function delete(): int
    {
        if (!$this->wheres) throw new LogicException('DELETE tanpa WHERE ditolak');
        return Database::query("DELETE FROM {$this->table}" . $this->whereSql(), $this->binds)->rowCount();
    }
}
