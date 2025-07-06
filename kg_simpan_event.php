<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak');
}

header('Content-Type: application/json');

// Koneksi database
$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal koneksi database']);
    exit;
}

// Ambil data JSON dari body
$data = json_decode(file_get_contents("php://input"), true);

$id          = $conn->real_escape_string($data['id']);
$title       = $conn->real_escape_string($data['title']);
$start       = $conn->real_escape_string($data['start']);
$end         = $conn->real_escape_string($data['end']);
$description = $conn->real_escape_string($data['description']);
$color       = $conn->real_escape_string($data['color']);

// Simpan data (insert / update)
if ($id === "") {
    $sql = "INSERT INTO kalender_kegiatan (title, start, end, description, color)
            VALUES ('$title', '$start', '$end', '$description', '$color')";
} else {
    $sql = "UPDATE kalender_kegiatan
            SET title='$title', start='$start', end='$end', description='$description', color='$color'
            WHERE id='$id'";
}

if ($conn->query($sql) === TRUE) {
    echo json_encode(['status' => 'berhasil']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan: ' . $conn->error]);
}

$conn->close();
?>
