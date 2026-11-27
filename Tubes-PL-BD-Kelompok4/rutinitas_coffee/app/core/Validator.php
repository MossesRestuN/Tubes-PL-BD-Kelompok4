<?php
// Validasi sisi server. Aturan: required|max:n|min:n|numeric|date|in:a,b|unique:tabel,kolom,kolom_pk
class Validator
{
    private array $errors = [];

    public static function make(array $data, array $rules, array $labels = [], ?int $ignoreId = null): self
    {
        $v = new self();
        foreach ($rules as $field => $ruleStr) {
            $label = $labels[$field] ?? ucfirst($field);
            $val   = (string)($data[$field] ?? '');
            $list  = $ruleStr === '' ? [] : explode('|', $ruleStr);
            $isNum = in_array('numeric', $list, true);
            foreach ($list as $r) {
                [$name, $arg] = array_pad(explode(':', $r, 2), 2, null);
                if ($name === 'required') {
                    if (trim($val) === '') { $v->errors[$field] = "$label wajib diisi."; break; }
                    continue;
                }
                if ($val === '') continue; // field opsional yang kosong dilewati
                $msg = match ($name) {
                    'max'     => mb_strlen($val) > (int)$arg ? "$label maksimal $arg karakter." : null,
                    'numeric' => !is_numeric($val) ? "$label harus berupa angka." : null,
                    'min'     => $isNum ? (is_numeric($val) && (float)$val < (float)$arg ? "$label minimal $arg." : null)
                                        : (mb_strlen($val) < (int)$arg ? "$label minimal $arg karakter." : null),
                    'date'    => !self::tanggalValid($val) ? "$label tidak valid." : null,
                    'in'      => !in_array($val, explode(',', $arg), true) ? "$label tidak valid." : null,
                    'unique'  => self::sudahAda($val, $arg, $ignoreId) ? "$label sudah dipakai." : null,
                    default   => null,
                };
                if ($msg) { $v->errors[$field] = $msg; break; }
            }
        }
        return $v;
    }

    private static function tanggalValid(string $v): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) === 1 && checkdate((int)substr($v, 5, 2), (int)substr($v, 8, 2), (int)substr($v, 0, 4));
    }

    private static function sudahAda(string $val, string $arg, ?int $ignoreId): bool
    {
        [$t, $c, $pk] = array_pad(explode(',', $arg), 3, null);
        $q = QueryBuilder::table($t)->where($c, '=', $val);
        if ($ignoreId !== null && $pk) $q->where($pk, '!=', $ignoreId);
        return $q->count() > 0;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
}
