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

    // --- FUNGSI BARU UNTUK MENGAMBIL DATA LAMA ---
    /**
     * Mengambil nilai perbandingan yang sudah tersimpan di database.
     */
    function getNilaiPerbandingan($conn, $kriteria1_id, $kriteria2_id) {
        $stmt = mysqli_prepare(
            $conn, 
            "SELECT nilai FROM perbandingan_kriteria 
             WHERE kriteria1 = ? AND kriteria2 = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $kriteria1_id, $kriteria2_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        // Mengembalikan nilai jika ada, atau null jika tidak ada
        return $row ? $row['nilai'] : null;
    }
    // ---------------------------------------------
    
    // Logika Penyimpanan Data (Tetap sama seperti UPDATE/INSERT sebelumnya)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai'])) {
        $success = true;
        
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO perbandingan_kriteria (kriteria1, kriteria2, nilai)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)"
        );
        mysqli_stmt_bind_param($stmt, 'iid', $id1_param, $id2_param, $v_param);

        foreach ($_POST['nilai'] as $id1 => $row) {
            foreach ($row as $id2 => $val) {
                if ($id1 == $id2 || $val === '' || $val == 0) continue;
                
                $v = floatval($val);
                
                // 1. Simpan nilai C1 vs C2
                $id1_param = $id1;
                $id2_param = $id2;
                $v_param = $v;
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false; break 2;
                }
                
                // 2. Simpan nilai kebalikannya C2 vs C1
                $id1_param = $id2;
                $id2_param = $id1;
                $v_param = (1.0 / $v); 
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false; break 2;
                }
            }
        }
        
        mysqli_stmt_close($stmt);

        if ($success) {
             echo "<div class='alert alert-success py-2'>Perbandingan kriteria berhasil **disimpan/diperbarui**.</div>";
        } else {
             echo "<div class='alert alert-danger py-2'>Terjadi kesalahan saat menyimpan perbandingan kriteria.</div>";
        }
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
                                    <?php 
                                        // Ambil nilai yang sudah ada dari DB
                                        $nilai_sekarang = getNilaiPerbandingan($conn, $k1['id'], $k2['id']);
                                    ?>
                                    <input type="number" step="0.1" min="1" max="9"
                                        name="nilai[<?= $k1['id']; ?>][<?= $k2['id']; ?>]"
                                        class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($nilai_sekarang ?? ''); ?>"> 
                                <?php else: // Kriteria K1 vs Kriteria K2 (K1 > K2, Nilai Kebalikan) ?>
                                    <?php 
                                        // Ambil nilai yang diinput di diagonal atas (K2 vs K1)
                                        $nilai_kebalikan = getNilaiPerbandingan($conn, $k2['id'], $k1['id']);
                                        
                                        // Tentukan hasil numerik, jika null gunakan '—'
                                        $tampilan_kebalikan = $nilai_kebalikan ? number_format(1 / $nilai_kebalikan, 4) : '—';
                                    ?>
                                    <div class="text-center">
                                        <span class="d-block text-xs text-muted">
                                            (<?= $k2['kode']; ?> vs <?= $k1['kode']; ?>)
                                        </span>
                                        </div>
                                    <div class="text-center">
                                        <span class="d-block fw-bold text-success">
                                            <?= $tampilan_kebalikan ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button class="btn btn-success btn-sm mt-2">Simpan/Perbarui Perbandingan</button>
    </form>
    <?php endif; ?>
</div>