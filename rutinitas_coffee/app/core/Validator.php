<?php
// Validasi sisi server. Aturan: required|max:n|min:n|numeric|in:a,b|unique:tabel,kolom,kolom_pk
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
                $msg = null;
                switch ($name) {
                    case 'max':
                        if (mb_strlen($val) > (int)$arg) $msg = "$label maksimal $arg karakter.";
                        break;
                    case 'numeric':
                        if (!is_numeric($val)) $msg = "$label harus berupa angka.";
                        break;
                    case 'min':
                        if ($isNum) { if (is_numeric($val) && (float)$val < (float)$arg) $msg = "$label minimal $arg."; }
                        elseif (mb_strlen($val) < (int)$arg) $msg = "$label minimal $arg karakter.";
                        break;
                    case 'in':
                        if (!in_array($val, explode(',', $arg), true)) $msg = "$label tidak valid.";
                        break;
                    case 'unique':
                        [$t, $c, $pk] = array_pad(explode(',', $arg), 3, null);
                        $q = QueryBuilder::table($t)->where($c, '=', $val);
                        if ($ignoreId !== null && $pk) $q->where($pk, '!=', $ignoreId);
                        if ($q->count() > 0) $msg = "$label sudah dipakai.";
                        break;
                }
                if ($msg) { $v->errors[$field] = $msg; break; }
            }
        }
        return $v;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
}
