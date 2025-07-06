<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

$sql = "SELECT * FROM acara ORDER BY tanggal ASC";
$result = mysqli_query($conn, $sql);

$events = [];

while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        'title' => $row['judul'],
        'start' => $row['tanggal']
    ];
}

echo json_encode($events);
