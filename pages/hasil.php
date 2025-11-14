<?php
// ==== Helper AHP ====

function getPriorityVector($matrix) {
    $keys = array_keys($matrix);
    $n = count($keys);

    // Hitung sum tiap kolom
    $columnSums = [];
    foreach ($keys as $j) {
        $columnSums[$j] = 0;
        foreach ($keys as $i) {
            $columnSums[$j] += $matrix[$i][$j];
        }
    }

    $priority = [];
    foreach ($keys as $i) {
        $rowSum = 0;
        foreach ($keys as $j) {
            if ($columnSums[$j] == 0) continue;
            $rowSum += $matrix[$i][$j] / $columnSums[$j];
        }
        $priority[$i] = $rowSum / $n;
    }
    return $priority;
}

function checkConsistency($matrix, $priority) {
    $keys = array_keys($matrix);
    $n = count($keys);

    $lambdaMax = 0;
    foreach ($keys as $i) {
        $rowSum = 0;
        foreach ($keys as $j) {
            $rowSum += $matrix[$i][$j] * $priority[$j];
        }
        if ($priority[$i] != 0) {
            $lambdaMax += $rowSum / $priority[$i];
        }
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
    $RI = $RI_table[$n] ?? 1.49;
    $CR = $RI == 0 ? 0 : $CI / $RI;

    return [
        'lambda_max' => $lambdaMax,
        'CI' => $CI,
        'CR' => $CR
    ];
}

function asPercent($v) {
    return round($v * 100, 2) . '%';
}

// ==== Ambil Data dari DB ====

$kriteria = [];
$qK = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($qK)) {
    $kriteria[$row['id']] = $row;
}

$alternatif = [];
$qA = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($qA)) {
    $alternatif[$row['id']] = $row;
}

$kriteriaIds = array_keys($kriteria);
$altIds      = array_keys($alternatif);

if (count($kriteriaIds) == 0 || count($altIds) == 0) {
    echo "<div class='card-custom'><div class='alert alert-warning'>Data kriteria atau alternatif belum lengkap.</div></div>";
    return;
}

// ==== Matriks Perbandingan Kriteria ====

$criteriaMatrix = [];
foreach ($kriteriaIds as $i) {
    foreach ($kriteriaIds as $j) {
        $criteriaMatrix[$i][$j] = ($i == $j) ? 1 : 0;
    }
}

$qPK = mysqli_query($conn, "SELECT * FROM perbandingan_kriteria");
while ($row = mysqli_fetch_assoc($qPK)) {
    $k1 = $row['kriteria1'];
    $k2 = $row['kriteria2'];
    $nilai = (float)$row['nilai'];
    if ($k1 == $k2) continue;
    $criteriaMatrix[$k1][$k2] = $nilai;
    $criteriaMatrix[$k2][$k1] = 1 / $nilai;
}

// Isi yang kosong dengan 1
foreach ($kriteriaIds as $i) {
    foreach ($kriteriaIds as $j) {
        if ($criteriaMatrix[$i][$j] == 0) {
            $criteriaMatrix[$i][$j] = 1;
            $criteriaMatrix[$j][$i] = 1;
        }
    }
}

$criteriaWeights     = getPriorityVector($criteriaMatrix);
$criteriaConsistency = checkConsistency($criteriaMatrix, $criteriaWeights);

// ==== Matriks Perbandingan Alternatif per Kriteria ====

$altWeightsPerCriteria = [];
$altConsistency        = [];

foreach ($kriteriaIds as $kid) {
    $altMatrix = [];
    foreach ($altIds as $i) {
        foreach ($altIds as $j) {
            $altMatrix[$i][$j] = ($i == $j) ? 1 : 0;
        }
    }

    $qPA = mysqli_query($conn, "SELECT * FROM perbandingan_alternatif WHERE kriteria_id = $kid");
    while ($row = mysqli_fetch_assoc($qPA)) {
        $a1 = $row['alt1'];
        $a2 = $row['alt2'];
        $nilai = (float)$row['nilai'];
        if ($a1 == $a2) continue;
        $altMatrix[$a1][$a2] = $nilai;
        $altMatrix[$a2][$a1] = 1 / $nilai;
    }

    foreach ($altIds as $i) {
        foreach ($altIds as $j) {
            if ($altMatrix[$i][$j] == 0) {
                $altMatrix[$i][$j] = 1;
                $altMatrix[$j][$i] = 1;
            }
        }
    }

    $altWeightsPerCriteria[$kid] = getPriorityVector($altMatrix);
    $altConsistency[$kid]        = checkConsistency($altMatrix, $altWeightsPerCriteria[$kid]);
}

// ==== Skor Akhir ====

$finalScores = [];
foreach ($altIds as $aid) {
    $score = 0;
    foreach ($kriteriaIds as $kid) {
        $score += ($criteriaWeights[$kid] ?? 0) * ($altWeightsPerCriteria[$kid][$aid] ?? 0);
    }
    $finalScores[$aid] = $score;
}
arsort($finalScores);

// ==== Analisis Narasi ====

$maxCritId   = array_keys($criteriaWeights, max($criteriaWeights))[0] ?? null;
$minCritId   = array_keys($criteriaWeights, min($criteriaWeights))[0] ?? null;
$bestAltId   = array_keys($finalScores, max($finalScores))[0] ?? null;
$worstAltId  = array_keys($finalScores, min($finalScores))[0] ?? null;

$altBestPerCrit = [];
$altWorstPerCrit = [];

foreach ($kriteriaIds as $kid) {
    $w = $altWeightsPerCriteria[$kid];
    arsort($w);
    $keys = array_keys($w);
    $altBestPerCrit[$kid]  = $keys[0];
    $altWorstPerCrit[$kid] = $keys[count($keys) - 1];
}
?>

<div class="card-custom">
    <h2 class="text-lg font-semibold mb-3">🏁 Hasil Perhitungan AHP & Rekomendasi Kos</h2>

    <h5 class="mb-2">Bobot Kriteria</h5>
    <table class="table table-sm table-bordered mb-2">
        <thead class="table-dark">
        <tr>
            <th>Kode</th>
            <th>Nama Kriteria</th>
            <th>Bobot</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($kriteriaIds as $kid): ?>
            <tr>
                <td><?= htmlspecialchars($kriteria[$kid]['kode']); ?></td>
                <td><?= htmlspecialchars($kriteria[$kid]['nama']); ?></td>
                <td><?= asPercent($criteriaWeights[$kid]); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $CR = $criteriaConsistency['CR'];
    $status = $CR < 0.1 ? 'KONSISTEN' : 'TIDAK KONSISTEN';
    $badge = $CR < 0.1 ? 'bg-success' : 'bg-danger';
    ?>
    <p class="text-sm">
        Rasio Konsistensi Kriteria (CR):
        <span class="badge <?= $badge; ?> text-white"><?= round($CR, 4) . " ($status)"; ?></span>
    </p>
</div>

<div class="card-custom">
    <h5 class="mb-2">Bobot Alternatif per Kriteria</h5>

    <?php foreach ($kriteriaIds as $kid): ?>
        <h6 class="mt-2 mb-1">
            <?= htmlspecialchars($kriteria[$kid]['kode']) . ' - ' . htmlspecialchars($kriteria[$kid]['nama']); ?>
        </h6>
        <table class="table table-sm table-bordered mb-1">
            <thead class="table-secondary">
            <tr>
                <th>Alternatif</th>
                <th>Bobot</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($altIds as $aid): ?>
                <tr>
                    <td><?= htmlspecialchars($alternatif[$aid]['kode'] . ' - ' . $alternatif[$aid]['nama']); ?></td>
                    <td><?= asPercent($altWeightsPerCriteria[$kid][$aid]); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $crAlt = $altConsistency[$kid]['CR'];
        $statusAlt = $crAlt < 0.1 ? 'KONSISTEN' : 'TIDAK KONSISTEN';
        $badgeAlt = $crAlt < 0.1 ? 'bg-success' : 'bg-danger';
        ?>
        <p class="text-xs mb-2">
            CR alternatif utk kriteria ini:
            <span class="badge <?= $badgeAlt; ?> text-white"><?= round($crAlt, 4) . " ($statusAlt)"; ?></span>
        </p>
    <?php endforeach; ?>
</div>

<div class="card-custom">
    <h5 class="mb-2">Ranking Akhir Alternatif Kos</h5>
    <table class="table table-sm table-bordered mb-2">
        <thead class="table-dark">
        <tr>
            <th>Ranking</th>
            <th>Kode</th>
            <th>Nama Kos</th>
            <th>Skor Akhir</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $rank = 1;
        foreach ($finalScores as $aid => $score): ?>
            <tr class="<?= $rank == 1 ? 'table-success' : ''; ?>">
                <td><?= $rank; ?></td>
                <td><?= htmlspecialchars($alternatif[$aid]['kode']); ?></td>
                <td><?= htmlspecialchars($alternatif[$aid]['nama']); ?></td>
                <td><?= asPercent($score); ?></td>
            </tr>
        <?php
        $rank++;
        endforeach; ?>
        </tbody>
    </table>

    <?php if ($bestAltId && $worstAltId): ?>
        <p class="text-sm">
            <strong>Kesimpulan:</strong><br>
            Alternatif terbaik berdasarkan metode AHP adalah
            <strong><?= htmlspecialchars($alternatif[$bestAltId]['kode'] . ' - ' . $alternatif[$bestAltId]['nama']); ?></strong>
            dengan skor sekitar <strong><?= asPercent($finalScores[$bestAltId]); ?></strong>.
            Alternatif dengan skor terendah adalah
            <strong><?= htmlspecialchars($alternatif[$worstAltId]['kode'] . ' - ' . $alternatif[$worstAltId]['nama']); ?></strong>.
        </p>
    <?php endif; ?>
</div>

<div class="card-custom">
    <h5 class="mb-2">Deskripsi Perbandingan & Interpretasi</h5>

    <?php if ($maxCritId): ?>
        <p class="text-sm">
            1️⃣ <strong>Kriteria Paling Berpengaruh:</strong><br>
            Kriteria <strong><?= htmlspecialchars($kriteria[$maxCritId]['nama']); ?></strong>
            memiliki bobot terbesar yaitu <?= asPercent($criteriaWeights[$maxCritId]); ?>.
            Artinya, aspek ini paling dominan dalam penentuan kos.
        </p>
    <?php endif; ?>

    <?php if ($minCritId && $minCritId != $maxCritId): ?>
        <p class="text-sm">
            2️⃣ <strong>Kriteria dengan Prioritas Terendah:</strong><br>
            Kriteria <strong><?= htmlspecialchars($kriteria[$minCritId]['nama']); ?></strong>
            memiliki bobot terkecil yaitu <?= asPercent($criteriaWeights[$minCritId]); ?>,
            sehingga pengaruhnya terhadap keputusan akhir relatif lebih kecil dibandingkan kriteria lain.
        </p>
    <?php endif; ?>

    <p class="text-sm">
        3️⃣ <strong>Alternatif Unggul per Kriteria:</strong><br>
    </p>
    <ul class="text-sm">
        <?php foreach ($kriteriaIds as $kid): ?>
            <?php
            $bestA  = $altBestPerCrit[$kid];
            $worstA = $altWorstPerCrit[$kid];
            ?>
            <li class="mb-1">
                Untuk kriteria <strong><?= htmlspecialchars($kriteria[$kid]['nama']); ?></strong>:
                alternatif terbaik adalah
                <strong><?= htmlspecialchars($alternatif[$bestA]['kode'] . ' - ' . $alternatif[$bestA]['nama']); ?></strong>
                (<?= asPercent($altWeightsPerCriteria[$kid][$bestA]); ?>),
                sedangkan yang paling rendah adalah
                <strong><?= htmlspecialchars($alternatif[$worstA]['kode'] . ' - ' . $alternatif[$worstA]['nama']); ?></strong>
                (<?= asPercent($altWeightsPerCriteria[$kid][$worstA]); ?>).
            </li>
        <?php endforeach; ?>
    </ul>

    <p class="text-sm">
        4️⃣ <strong>Makna Umum:</strong><br>
        - Selisih bobot yang besar antar alternatif pada satu kriteria menunjukkan adanya perbedaan kualitas yang jelas
          (misalnya satu kos jauh lebih murah atau jauh lebih aman).<br>
        - Jika semua CR &lt; 0.1, maka penilaian perbandingan yang dimasukkan sudah cukup konsisten dan
          hasil perhitungan AHP dapat dipercaya.
    </p>
</div>
