<?php
// =============================================
// SPK PEMILIHAN KOS MENGGUNAKAN METODE AHP
// PHP NATIVE - 1 HALAMAN WEB
// =============================================

// ---------------------------
// 1. Data Kriteria & Alternatif
// ---------------------------

$criteria = [
    'C1' => 'Harga',
    'C2' => 'Jarak ke Kampus',
    'C3' => 'Fasilitas',
    'C4' => 'Keamanan'
];

$alternatives = [
    'K1' => 'Kos A (dekat kampus)',
    'K2' => 'Kos B (fasilitas lengkap)',
    'K3' => 'Kos C (paling murah)'
];

// ---------------------------
// 2. Matriks Perbandingan Kriteria
//    (contoh penilaian, bisa kamu ganti)
// ---------------------------
// Keterangan penilaian (subjektif):
// - Harga sedikit lebih penting dari Jarak -> 3
// - Harga sedikit lebih penting dari Fasilitas -> 2
// - Keamanan lebih penting dari Harga -> 3 (jadi C1 vs C4 = 1/3)
// - Fasilitas lebih penting dari Jarak, dst.

$criteriaComparison = [
    'C1' => ['C1' => 1,   'C2' => 3,   'C3' => 2,   'C4' => 1/3],
    'C2' => ['C1' => 1/3, 'C2' => 1,   'C3' => 1/2, 'C4' => 1/5],
    'C3' => ['C1' => 1/2, 'C2' => 2,   'C3' => 1,   'C4' => 1/3],
    'C4' => ['C1' => 3,   'C2' => 5,   'C3' => 3,   'C4' => 1],
];

// ---------------------------
// 3. Matriks Perbandingan Alternatif
//    untuk setiap kriteria
// ---------------------------

// C1: Harga (semakin murah semakin baik)
// Misal: K3 paling murah, K1 menengah, K2 paling mahal
$altComparison_C1 = [
    'K1' => ['K1' => 1,   'K2' => 3,   'K3' => 1/3],
    'K2' => ['K1' => 1/3, 'K2' => 1,   'K3' => 1/5],
    'K3' => ['K1' => 3,   'K2' => 5,   'K3' => 1],
];

// C2: Jarak ke Kampus (semakin dekat semakin baik)
// Misal: K2 paling dekat, K1 agak dekat, K3 paling jauh
$altComparison_C2 = [
    'K1' => ['K1' => 1,   'K2' => 1/3, 'K3' => 3],
    'K2' => ['K1' => 3,   'K2' => 1,   'K3' => 5],
    'K3' => ['K1' => 1/3, 'K2' => 1/5, 'K3' => 1],
];

// C3: Fasilitas (semakin lengkap semakin baik)
// Misal: K3 terbaik, K2 sedang, K1 biasa
$altComparison_C3 = [
    'K1' => ['K1' => 1,   'K2' => 1/3, 'K3' => 1/5],
    'K2' => ['K1' => 3,   'K2' => 1,   'K3' => 1/3],
    'K3' => ['K1' => 5,   'K2' => 3,   'K3' => 1],
];

// C4: Keamanan (semakin aman semakin baik)
// Misal: K2 paling aman (CCTV, penjaga), K1 sedang, K3 kurang
$altComparison_C4 = [
    'K1' => ['K1' => 1,   'K2' => 1/3, 'K3' => 3],
    'K2' => ['K1' => 3,   'K2' => 1,   'K3' => 5],
    'K3' => ['K1' => 1/3, 'K2' => 1/5, 'K3' => 1],
];

// ---------------------------
// 4. Fungsi AHP: Priority Vector & Konsistensi
// ---------------------------

function getPriorityVector($matrix) {
    $keys = array_keys($matrix);
    $n = count($keys);

    // Hitung jumlah tiap kolom
    $columnSums = array_fill_keys($keys, 0);
    foreach ($keys as $i) {
        foreach ($keys as $j) {
            $columnSums[$j] += $matrix[$i][$j];
        }
    }

    // Normalisasi dan hitung rata-rata baris
    $priority = [];
    foreach ($keys as $i) {
        $rowSum = 0;
        foreach ($keys as $j) {
            $normalized = $matrix[$i][$j] / $columnSums[$j];
            $rowSum += $normalized;
        }
        $priority[$i] = $rowSum / $n;
    }

    return $priority;
}

function checkConsistency($matrix, $priority) {
    $keys = array_keys($matrix);
    $n = count($keys);

    // Hitung λmax
    $lambdaMax = 0;
    foreach ($keys as $i) {
        $rowSum = 0;
        foreach ($keys as $j) {
            $rowSum += $matrix[$i][$j] * $priority[$j];
        }
        $lambdaMax += $rowSum / $priority[$i];
    }
    $lambdaMax = $lambdaMax / $n;

    $CI = ($lambdaMax - $n) / ($n - 1);

    $RI_table = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49
    ];
    $RI = $RI_table[$n];
    $CR = $RI == 0 ? 0 : $CI / $RI;

    return [
        'lambda_max' => $lambdaMax,
        'CI' => $CI,
        'CR' => $CR
    ];
}

// ---------------------------
// 5. Hitung Bobot Kriteria
// ---------------------------

$criteriaWeights = getPriorityVector($criteriaComparison);
$criteriaConsistency = checkConsistency($criteriaComparison, $criteriaWeights);

// ---------------------------
// 6. Hitung Bobot Alternatif per Kriteria
// ---------------------------

$altWeights_C1 = getPriorityVector($altComparison_C1);
$cons_C1 = checkConsistency($altComparison_C1, $altWeights_C1);

$altWeights_C2 = getPriorityVector($altComparison_C2);
$cons_C2 = checkConsistency($altComparison_C2, $altWeights_C2);

$altWeights_C3 = getPriorityVector($altComparison_C3);
$cons_C3 = checkConsistency($altComparison_C3, $altWeights_C3);

$altWeights_C4 = getPriorityVector($altComparison_C4);
$cons_C4 = checkConsistency($altComparison_C4, $altWeights_C4);

// ---------------------------
// 7. Hitung Skor Akhir Alternatif
// ---------------------------

$finalScores = [];
foreach ($alternatives as $aKey => $aName) {
    $score =
        ($criteriaWeights['C1'] * $altWeights_C1[$aKey]) +
        ($criteriaWeights['C2'] * $altWeights_C2[$aKey]) +
        ($criteriaWeights['C3'] * $altWeights_C3[$aKey]) +
        ($criteriaWeights['C4'] * $altWeights_C4[$aKey]);

    $finalScores[$aKey] = $score;
}

// Urutkan dari terbesar ke terkecil
arsort($finalScores);

// Helper untuk format persen
function asPercent($value) {
    return round($value * 100, 2) . '%';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK Pemilihan Kos - Metode AHP (PHP Native)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,.1);
        }
        h1, h2, h3 {
            margin-top: 0;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: center;
        }
        th {
            background: #2c3e50;
            color: #fff;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-left: 5px;
        }
        .badge-ok {
            background: #2ecc71;
            color: #fff;
        }
        .badge-bad {
            background: #e74c3c;
            color: #fff;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .highlight {
            background: #ecf9ff;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>SPK Pemilihan Kos<br><small>Metode AHP (PHP Native)</small></h1>

    <div class="card">
        <h3>Tujuan</h3>
        <p>Menentukan kos terbaik berdasarkan kriteria: Harga, Jarak ke Kampus, Fasilitas, dan Keamanan.</p>
    </div>

    <div class="card">
        <h3>Data Kriteria</h3>
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama Kriteria</th>
                <th>Bobot AHP</th>
            </tr>
            <?php foreach ($criteria as $cKey => $cName): ?>
                <tr>
                    <td><?php echo $cKey; ?></td>
                    <td style="text-align:left;"><?php echo $cName; ?></td>
                    <td><?php echo asPercent($criteriaWeights[$cKey]); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php
        $CR = $criteriaConsistency['CR'];
        $status = $CR < 0.1 ? 'KONSISTEN' : 'TIDAK KONSISTEN';
        $class = $CR < 0.1 ? 'badge-ok' : 'badge-bad';
        ?>
        <p>
            Rasio Konsistensi Kriteria (CR):
            <strong><?php echo round($CR, 4); ?></strong>
            <span class="badge <?php echo $class; ?>"><?php echo $status; ?></span>
        </p>
    </div>

    <div class="card">
        <h3>Data Alternatif Kos</h3>
        <table>
            <tr>
                <th>Kode</th>
                <th>Nama Kos</th>
            </tr>
            <?php foreach ($alternatives as $aKey => $aName): ?>
                <tr>
                    <td><?php echo $aKey; ?></td>
                    <td style="text-align:left;"><?php echo $aName; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h3>Bobot Alternatif per Kriteria</h3>

        <h4>C1 - Harga</h4>
        <table>
            <tr>
                <th>Alternatif</th>
                <th>Bobot</th>
            </tr>
            <?php foreach ($altWeights_C1 as $aKey => $w): ?>
                <tr>
                    <td style="text-align:left;"><?php echo $alternatives[$aKey]; ?></td>
                    <td><?php echo asPercent($w); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h4>C2 - Jarak ke Kampus</h4>
        <table>
            <tr>
                <th>Alternatif</th>
                <th>Bobot</th>
            </tr>
            <?php foreach ($altWeights_C2 as $aKey => $w): ?>
                <tr>
                    <td style="text-align:left;"><?php echo $alternatives[$aKey]; ?></td>
                    <td><?php echo asPercent($w); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h4>C3 - Fasilitas</h4>
        <table>
            <tr>
                <th>Alternatif</th>
                <th>Bobot</th>
            </tr>
            <?php foreach ($altWeights_C3 as $aKey => $w): ?>
                <tr>
                    <td style="text-align:left;"><?php echo $alternatives[$aKey]; ?></td>
                    <td><?php echo asPercent($w); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h4>C4 - Keamanan</h4>
        <table>
            <tr>
                <th>Alternatif</th>
                <th>Bobot</th>
            </tr>
            <?php foreach ($altWeights_C4 as $aKey => $w): ?>
                <tr>
                    <td style="text-align:left;"><?php echo $alternatives[$aKey]; ?></td>
                    <td><?php echo asPercent($w); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="card">
        <h3>Hasil Akhir & Ranking Kos</h3>
        <table>
            <tr>
                <th>Ranking</th>
                <th>Alternatif</th>
                <th>Skor Akhir</th>
            </tr>
            <?php
            $rank = 1;
            foreach ($finalScores as $aKey => $score):
            ?>
                <tr class="<?php echo $rank == 1 ? 'highlight' : ''; ?>">
                    <td><?php echo $rank; ?></td>
                    <td style="text-align:left;"><?php echo $alternatives[$aKey]; ?></td>
                    <td><?php echo asPercent($score); ?></td>
                </tr>
            <?php
                $rank++;
            endforeach;
            ?>
        </table>

        <p><strong>Kesimpulan:</strong><br>
            Kos dengan <strong>ranking 1</strong> adalah rekomendasi terbaik berdasarkan metode AHP dan penilaian yang digunakan pada contoh ini.
        </p>
    </div>

    <div class="footer">
        Contoh implementasi AHP untuk pemilihan kos menggunakan PHP Native.<br>
        Silakan sesuaikan nilai perbandingan dengan kondisi nyata kos di sekitar kampusmu.
    </div>
</div>
</body>
</html>
