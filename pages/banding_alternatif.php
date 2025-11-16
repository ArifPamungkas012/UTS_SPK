<div class="card-custom">
    <h2 class="text-lg font-semibold mb-3">⚖️ Perbandingan Alternatif per Kriteria</h2>

    <?php
    // Asumsi koneksi $conn sudah tersedia

    // Ambil kriteria untuk dropdown
    $kriteria = [];
    $qk = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($qk)) {
        $kriteria[] = $row;
    }

    if (count($kriteria) == 0) {
        echo "<div class='alert alert-warning'>Belum ada kriteria. Tambahkan kriteria terlebih dahulu.</div>";
        return;
    }

    // Tentukan Kriteria yang dipilih
    $kriteriaIdDipilih = $_GET['kriteria_id'] ?? $kriteria[0]['id'];
    $kriteriaIdDipilih = intval($kriteriaIdDipilih); // Pastikan integer

    // Ambil alternatif
    $alternatif = [];
    $qa = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($qa)) {
        $alternatif[] = $row;
    }

    if (count($alternatif) < 2) {
        echo "<div class='alert alert-warning'>Minimal 2 alternatif diperlukan untuk perbandingan.</div>";
        return;
    }

    // --- FUNGSI BARU UNTUK MENGAMBIL DATA LAMA ---
    /**
     * Mengambil nilai perbandingan alternatif yang sudah tersimpan.
     */
    function getNilaiPerbandinganAlternatif($conn, $kriteria_id, $alt1_id, $alt2_id) {
        $stmt = mysqli_prepare(
            $conn, 
            "SELECT nilai FROM perbandingan_alternatif 
             WHERE kriteria_id = ? AND alt1 = ? AND alt2 = ?"
        );
        // Pastikan format tipe data (i = integer, d = double) sesuai
        mysqli_stmt_bind_param($stmt, 'iii', $kriteria_id, $alt1_id, $alt2_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? $row['nilai'] : null;
    }
    // ---------------------------------------------


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai']) && isset($_POST['kriteria_id'])) {
        $success = true;
        $kid = intval($_POST['kriteria_id']);
        
        // --- GANTI DELETE DAN INSERT DENGAN INSERT... ON DUPLICATE KEY UPDATE ---
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)"
        );
        mysqli_stmt_bind_param($stmt, 'iiid', $kid_param, $id1_param, $id2_param, $v_param);

        foreach ($_POST['nilai'] as $id1 => $row) {
            foreach ($row as $id2 => $val) {
                // Hanya memproses input diagonal atas
                if ($id1 == $id2 || $val === '' || $val == 0) continue;
                
                $v = floatval($val);
                
                // 1. Simpan nilai perbandingan Alt1 vs Alt2
                $kid_param = $kid;
                $id1_param = intval($id1);
                $id2_param = intval($id2);
                $v_param = $v;
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false; break 2;
                }
                
                // 2. Simpan nilai kebalikannya Alt2 vs Alt1
                $id1_param = intval($id2);
                $id2_param = intval($id1);
                $v_param = (1.0 / $v); 
                if (!mysqli_stmt_execute($stmt)) {
                    $success = false; break 2;
                }
            }
        }
        
        mysqli_stmt_close($stmt);

        if ($success) {
            echo "<div class='alert alert-success py-2'>Perbandingan alternatif untuk kriteria terpilih berhasil **disimpan/diperbarui**.</div>";
        } else {
            echo "<div class='alert alert-danger py-2'>Terjadi kesalahan saat menyimpan perbandingan.</div>";
        }
        $kriteriaIdDipilih = $kid;
    }
    ?>
    
    <form method="get" class="mb-3">
        <input type="hidden" name="page" value="banding_alternatif">
        <label class="form-label">Pilih Kriteria</label>
        <select name="kriteria_id" class="form-select form-select-sm w-auto d-inline-block">
            <?php foreach ($kriteria as $k): ?>
                <option value="<?= $k['id']; ?>"
                    <?= $k['id'] == $kriteriaIdDipilih ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($k['kode'] . ' - ' . $k['nama']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm">Tampilkan</button>
    </form>

    <p class="text-sm text-gray-600 mb-2">
        Kriteria terpilih:
        <strong>
            <?php
            // Cari kriteria yang sedang aktif untuk ditampilkan namanya
            $kNow = array_values(array_filter($kriteria, fn($kk) => $kk['id'] == $kriteriaIdDipilih))[0] ?? null;
            echo $kNow ? htmlspecialchars($kNow['kode'] . ' - ' . $kNow['nama']) : '';
            ?>
        </strong>
    </p>

    <form method="post">
        <input type="hidden" name="kriteria_id" value="<?= $kriteriaIdDipilih; ?>">

        <table class="table table-sm table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Alternatif</th>
                    <?php foreach ($alternatif as $a): ?>
                        <th><?= htmlspecialchars($a['kode']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alternatif as $a1): ?>
                    <tr>
                        <th class="table-secondary">
                            <?= htmlspecialchars($a1['kode']); ?>
                        </th>
                        <?php foreach ($alternatif as $a2): ?>
                            <td>
                                <?php if ($a1['id'] == $a2['id']): ?>
                                    1
                                <?php elseif ($a1['id'] < $a2['id']): ?>
                                    <?php 
                                        // 💡 Ambil nilai lama untuk diisi ke input
                                        $nilai_sekarang = getNilaiPerbandinganAlternatif($conn, $kriteriaIdDipilih, $a1['id'], $a2['id']);
                                    ?>
                                    <input type="number" step="0.1" min="1" max="9"
                                        name="nilai[<?= $a1['id']; ?>][<?= $a2['id']; ?>]"
                                        class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($nilai_sekarang ?? ''); ?>">
                                <?php else: 
                                    // 💡 Tampilan nilai kebalikan (Non-editable)
                                    $nilai_kebalikan = getNilaiPerbandinganAlternatif($conn, $kriteriaIdDipilih, $a2['id'], $a1['id']);
                                    $tampilan_kebalikan = $nilai_kebalikan ? number_format(1 / $nilai_kebalikan, 4) : '—';
                                ?>
                                    <div class="text-center">
                                        <span class="d-block text-xs text-muted">
                                            (<?= $a2['kode']; ?> vs <?= $a1['kode']; ?>)
                                        </span>
                                        
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
</div>