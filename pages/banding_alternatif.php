<div class="card-custom">
    <h2 class="text-lg font-semibold mb-3">⚖️ Perbandingan Alternatif per Kriteria</h2>

    <?php
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

    $kriteriaIdDipilih = $_GET['kriteria_id'] ?? $kriteria[0]['id'];

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nilai']) && isset($_POST['kriteria_id'])) {
        $kid = intval($_POST['kriteria_id']);
        mysqli_query($conn, "DELETE FROM perbandingan_alternatif WHERE kriteria_id = $kid");

        foreach ($_POST['nilai'] as $id1 => $row) {
            foreach ($row as $id2 => $val) {
                if ($id1 == $id2 || $val === '' || $val == 0) continue;
                $v = floatval($val);
                mysqli_query(
                    $conn,
                    "INSERT INTO perbandingan_alternatif (kriteria_id, alt1, alt2, nilai)
                     VALUES ($kid, $id1, $id2, $v)"
                );
            }
        }

        echo "<div class='alert alert-success py-2'>Perbandingan alternatif untuk kriteria terpilih berhasil disimpan.</div>";
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
                                    <input type="number" step="0.1" min="1" max="9"
                                           name="nilai[<?= $a1['id']; ?>][<?= $a2['id']; ?>]"
                                           class="form-control form-control-sm">
                                <?php else: ?>
                                    <span class="text-muted text-xs">1 / nilai (<?= $a2['kode']; ?> vs <?= $a1['kode']; ?>)</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button class="btn btn-success btn-sm mt-2">Simpan Perbandingan</button>
    </form>
</div>
