<?php
session_start();
if (!isset($_SESSION['login'])) {
    http_response_code(403);
    exit('Akses ditolak');
}

require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? null;
    $judul = $_POST['judul'] ?? null;

    if (!$tanggal || !$judul) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit();
    }

    // Periksa apakah acara sudah ada
    $stmt = $conn->prepare("SELECT id FROM acara WHERE tanggal = ?");
    $stmt->bind_param("s", $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update jika sudah ada
        $stmt = $conn->prepare("UPDATE acara SET judul = ? WHERE tanggal = ?");
        $stmt->bind_param("ss", $judul, $tanggal);
    } else {
        // Tambah jika belum ada
        $stmt = $conn->prepare("INSERT INTO acara (tanggal, judul) VALUES (?, ?)");
        $stmt->bind_param("ss", $tanggal, $judul);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan acara']);
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo 'Metode tidak diizinkan';
}
