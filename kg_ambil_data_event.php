<?php
// Aktifkan tampilan error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    exit();
}

// Jika ada parameter ID, ambil satu acara
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM kalender_kegiatan WHERE id = $id";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Acara tidak ditemukan']);
    }
    $conn->close();
    exit();
}

// Ambil semua acara untuk ditampilkan di kalender
$query = "SELECT id, title, start, end, color FROM kalender_kegiatan";
$result = $conn->query($query);

$events = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id'    => $row['id'],
            'title' => $row['title'],
            'start' => $row['start'],
            'end'   => $row['end'],
            'color' => $row['color']
        ];
    }
    echo json_encode($events);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data acara']);
}

$conn->close();
?>
