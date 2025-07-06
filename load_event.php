<?php
include 'koneksi.php';

$events = [];

$query = "SELECT * FROM acara";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $events[] = [
        'title' => $row['judul'],
        'start' => $row['tanggal_mulai'],
        'end'   => $row['tanggal_selesai'],
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
?>
