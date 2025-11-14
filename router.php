<?php
function resolvePage()
{
    $allowed = [
        "dashboard",
        "kriteria",
        "alternatif",
        "banding_kriteria",
        "banding_alternatif",
        "hasil",
    ];

    $page = $_GET['page'] ?? "dashboard";

    if (!in_array($page, $allowed)) {
        $page = "dashboard";
    }

    $file = __DIR__ . "/pages/$page.php";

    if (!file_exists($file)) {
        return __DIR__ . "/pages/dashboard.php";
    }

    return $file;
}
