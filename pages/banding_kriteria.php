<div class="card-custom">
    <h2 class="text-lg font-semibold mb-3">⚖️ Perbandingan Kriteria (Pairwise)</h2>

    <p class="text-sm text-gray-600 mb-3">
        Masukkan nilai perbandingan berpasangan antar kriteria menggunakan skala 1–9
        (1 = sama penting, 3 = sedikit lebih penting, 5 = lebih penting, 7 = sangat lebih penting, 9 = mutlak lebih penting).
        Nilai kebalikannya akan dihitung otomatis.
    </p>

    <?php
    // Ambil semua kriteria
    $kriteria = [];
    $q = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($q)) {
        $kriteria[] = $row;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai'])) {
        // Hapus dulu semua data lama
        mysqli_query($conn, "TRUNCATE TABLE perbandingan_kriteria");

        foreach ($_POST['nilai'] as $id1 => $row) {
            foreach ($row as $id2 => $val) {
                if ($id1 == $id2 || $val === '' || $val == 0) continue;
                $v = floatval($val);
                mysqli_query(
                    $conn,
                    "INSERT INTO perbandingan_kriteria (kriteria1, kriteria2, nilai)
                     VALUES ($id1, $id2, $v)"
                );
            }
        }

        echo "<div class='alert alert-success py-2'>Perbandingan kriteria berhasil disimpan.</div>";
    }
    ?>

    <?php if (count($kriteria) < 2): ?>
        <div class="alert alert-warning">Minimal 2 kriteria diperlukan untuk perbandingan.</div>
    <?php else: ?>

    <form method="post">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Kriteria</th>
                    <?php foreach ($kriteria as $k): ?>
                        <th><?= htmlspecialchars($k['kode']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kriteria as $k1): ?>
                    <tr>
                        <th class="table-secondary"><?= htmlspecialchars($k1['kode']); ?></th>
                        <?php foreach ($kriteria as $k2): ?>
                            <td>
                                <?php if ($k1['id'] == $k2['id']): ?>
                                    1
                                <?php elseif ($k1['id'] < $k2['id']): ?>
                                    <input type="number" step="0.1" min="1" max="9"
                                           name="nilai[<?= $k1['id']; ?>][<?= $k2['id']; ?>]"
                                           class="form-control form-control-sm">
                                <?php else: ?>
                                    <!-- Bagian bawah diagonal, akan dihitung sebagai 1/x -->
                                    <span class="text-muted text-xs">1 / nilai (<?= $k2['kode']; ?> vs <?= $k1['kode']; ?>)</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button class="btn btn-success btn-sm mt-2">Simpan Perbandingan</button>
    </form>
    <?php endif; ?>
</div>
