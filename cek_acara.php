<?php
include 'koneksi.php';

$query = "SELECT * FROM acara ORDER BY tanggal_mulai DESC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

echo "<h2>Isi Tabel Acara</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Judul</th><th>Deskripsi</th><th>Tanggal Mulai</th><th>Tanggal Selesai</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['judul'] . "</td>";
    echo "<td>" . $row['deskripsi'] . "</td>";
    echo "<td>" . $row['tanggal_mulai'] . "</td>";
    echo "<td>" . $row['tanggal_selesai'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
