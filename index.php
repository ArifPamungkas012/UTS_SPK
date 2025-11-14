<?php
include "config.php";
include "router.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AHP Pemilihan Kos</title>

    <!-- Bootstrap 5 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">

<div class="d-flex h-screen">

    <!-- SIDEBAR -->
    <aside class="sidebar bg-gray-900 text-white d-flex flex-column shadow-lg">
        <div class="p-4 border-b border-gray-700 text-center">
            <h2 class="text-xl font-bold mb-0">🏠 AHP KOS</h2>
            <p class="text-xs text-gray-400 mt-1">Sistem Pendukung Keputusan</p>
        </div>

        <nav class="flex-1 p-3">
            <a href="index.php?page=dashboard" class="menu-link">📊 Dashboard</a>
            <a href="index.php?page=kriteria" class="menu-link">📌 Kriteria</a>
            <a href="index.php?page=alternatif" class="menu-link">🏠 Alternatif Kos</a>
            <a href="index.php?page=banding_kriteria" class="menu-link">⚖️ Perbandingan Kriteria</a>
            <a href="index.php?page=banding_alternatif" class="menu-link">⚖️ Perbandingan Alternatif</a>
            <a href="index.php?page=hasil" class="menu-link">🏁 Hasil Perhitungan</a>
        </nav>

        <footer class="p-3 text-center text-xs text-gray-400 border-t border-gray-700">
            &copy; <?= date('Y') ?> AHP Kos Decision System
        </footer>
    </aside>

    <!-- CONTENT -->
    <main class="flex-grow overflow-y-auto p-4">
        <?php include resolvePage(); ?>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="assets/js/app.js"></script>
</body>
</html>
