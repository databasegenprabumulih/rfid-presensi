<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require 'koneksi.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Hapus dari tabel kalender_kegiatan (bukan events)
    $hapus = mysqli_query($conn, "DELETE FROM kalender_kegiatan WHERE id = $id");

    if ($hapus) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Gagal menghapus data']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID tidak valid']);
}
