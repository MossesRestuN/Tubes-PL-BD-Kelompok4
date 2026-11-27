<?php
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } // cegah XSS
function url(string $p = ''): string { return BASE_URL . '/index.php?url=' . ltrim($p, '/'); }
function asset(string $f): string { return BASE_URL . '/assets/' . $f; }
function rupiah($n): string { return 'Rp' . number_format((float)$n, 0, ',', '.'); }
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">'; }
function flash(string $type, string $msg): void { $_SESSION['flash'][$type] = $msg; }
function get_flash(string $type): ?string {
    $m = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $m;
}

function icon(string $n): string {
    $p = [
        'gauge'  => '<circle cx="12" cy="12" r="9"/><path d="M12 12l4-4"/>',
        'list'   => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'cup'    => '<path d="M4 8h12v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5z"/><path d="M16 10h2a2 2 0 0 1 0 4h-2"/>',
        'box'    => '<path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        'book'   => '<path d="M4 4h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2z"/><path d="M8 4v16"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'wallet' => '<path d="M3 7h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 7l12-3v3"/><circle cx="16.5" cy="13.5" r="1"/>',
        'cart'   => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6"/>',
        'chart'  => '<path d="M4 20V4M4 20h16"/><path d="M8 16l4-5 3 3 5-7"/>',
        'alert'  => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18h.01"/>',
        'logout' => '<path d="M9 21H5V3h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
    ];
    return '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . ($p[$n] ?? '') . '</svg>';
}

// Waktu histori; jam disembunyikan untuk catatan tanggal lampau (diinput manual tanpa jam)
function fmt_waktu(string $dt): string
{
    $t = strtotime($dt);
    return date('d M Y', $t) . (date('H:i', $t) === '00:00' ? '' : ', ' . date('H:i', $t));
}

function fmt_singkat($v): string {
    $v = (float)$v;
    if ($v >= 1000000) return rtrim(rtrim(number_format($v / 1000000, 1, ',', '.'), '0'), ',') . ' jt';
    if ($v >= 1000) return number_format($v / 1000, 0, ',', '.') . ' rb';
    return (string)(int)$v;
}

// Grafik batang berkelompok dalam SVG (tanpa library eksternal, aman dipakai offline)
function bar_chart(array $labels, array $series, array $colors): string {
    $W = 640; $H = 300; $L = 56; $R = 10; $T = 14; $B = 32;
    $pw = $W - $L - $R; $ph = $H - $T - $B;
    $max = 0;
    foreach ($series as $vals) foreach ($vals as $v) $max = max($max, (float)$v);
    if ($max <= 0) return '<p class="muted">Belum ada data penjualan.</p>';
    $mag = pow(10, floor(log10($max)));
    $top = ceil($max / $mag) * $mag;
    $n = count($labels); $gw = $pw / $n; $k = count($series); $bw = min(($gw * 0.72) / $k, 60); // batang dibatasi (maks 60 unit viewBox, kira-kira 35px) agar tidak melar saat data sedikit
    $names = array_keys($series); $data = array_values($series);

    $s = '<svg viewBox="0 0 ' . $W . ' ' . $H . '" class="chart" role="img">';
    for ($i = 0; $i <= 5; $i++) {
        $y = round($T + $ph - ($ph * $i / 5), 1);
        $s .= '<line x1="' . $L . '" x2="' . ($W - $R) . '" y1="' . $y . '" y2="' . $y . '" class="gridline"/>';
        $s .= '<text x="' . ($L - 6) . '" y="' . ($y + 4) . '" text-anchor="end" class="axis">' . e(fmt_singkat($top * $i / 5)) . '</text>';
    }
    foreach ($labels as $i => $lab) {
        $x0 = $L + $gw * $i + ($gw - $bw * $k) / 2; // kelompok batang di tengah slot
        foreach ($data as $j => $vals) {
            $v = (float)($vals[$i] ?? 0);
            $h = $ph * $v / $top;
            if ($h > 0) {
                $s .= '<rect x="' . round($x0 + $bw * $j, 1) . '" y="' . round($T + $ph - $h, 1) . '" width="' . round($bw - 2, 1) . '" height="' . round($h, 1) . '" fill="' . e($colors[$j] ?? '#DC2626') . '"><title>' . e($names[$j] . ' ' . $lab . ': ' . rupiah($v)) . '</title></rect>';
            }
        }
        $s .= '<text x="' . round($L + $gw * $i + $gw / 2, 1) . '" y="' . ($H - 10) . '" text-anchor="middle" class="axis">' . e($lab) . '</text>';
    }
    return $s . '</svg>';
}