<?php
$jumlahKriteria   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kriteria"));
$jumlahAlternatif = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alternatif"));
?>
<div class="card-custom">
    <h2 class="text-xl font-semibold mb-2">📊 Dashboard</h2>
    <p class="text-gray-700 mb-0">
        Selamat datang di <strong>Sistem Pendukung Keputusan Pemilihan Kos</strong> menggunakan metode
        <strong>AHP (Analytic Hierarchy Process)</strong>.
        Gunakan menu di sebelah kiri untuk mengelola data dan melihat hasil perhitungan.
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="card-custom bg-blue-50 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Jumlah Kriteria</p>
                <p class="text-2xl font-bold text-blue-700"><?= $jumlahKriteria; ?></p>
            </div>
            <div class="text-3xl">📌</div>
        </div>
    </div>
    <div class="card-custom bg-green-50 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Jumlah Alternatif Kos</p>
                <p class="text-2xl font-bold text-green-700"><?= $jumlahAlternatif; ?></p>
            </div>
            <div class="text-3xl">🏠</div>
        </div>
    </div>
    <div class="card-custom bg-yellow-50 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Status Perhitungan</p>
                <p class="text-sm text-gray-700">
                    Lakukan perbandingan kriteria dan alternatif,
                    lalu buka menu <strong>Hasil Perhitungan</strong>.
                </p>
            </div>
            <div class="text-3xl">⚙️</div>
        </div>
    </div>
</div>
