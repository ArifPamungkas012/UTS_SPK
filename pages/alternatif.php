<div class="card-custom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-lg font-semibold mb-0">🏠 Data Alternatif Kos</h2>
        <a href="index.php?page=alternatif&tambah=1" class="btn btn-primary btn-sm">
            + Tambah Alternatif
        </a>
    </div>

    <?php
    if (isset($_GET['tambah']) && $_GET['tambah'] == 1): ?>
        <form method="post" class="bg-gray-50 p-3 rounded mb-3">
            <div class="mb-2">
                <label class="form-label">Kode Alternatif</label>
                <input type="text" name="kode" class="form-control form-control-sm" required>
            </div>
            <div class="mb-2">
                <label class="form-label">Nama Kos</label>
                <input type="text" name="nama" class="form-control form-control-sm" required>
            </div>
            <button class="btn btn-success btn-sm" name="save">Simpan</button>
            <a href="index.php?page=alternatif" class="btn btn-secondary btn-sm">Batal</a>
        </form>

        <?php
        if (isset($_POST['save'])) {
            $kode = mysqli_real_escape_string($conn, $_POST['kode']);
            $nama = mysqli_real_escape_string($conn, $_POST['nama']);
            mysqli_query($conn, "INSERT INTO alternatif (kode, nama) VALUES ('$kode', '$nama')");
            echo "<script>location='index.php?page=alternatif';</script>";
        }
        ?>

    <?php endif; ?>

    <table class="table table-sm table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 80px;">Kode</th>
                <th>Nama Kos</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id ASC");
            while ($r = mysqli_fetch_assoc($q)): ?>
                <tr>
                    <td><?= htmlspecialchars($r['kode']); ?></td>
                    <td><?= htmlspecialchars($r['nama']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
