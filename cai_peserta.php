<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$desa_filter = $_GET['desa'] ?? '';
$desa_list = mysqli_query($conn, "SELECT DISTINCT desa FROM peserta_cai WHERE desa IS NOT NULL ORDER BY desa ASC");

// Upload CSV
if (isset($_POST['upload_csv'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if ($_FILES['csv_file']['error'] === 0 && is_uploaded_file($file)) {
        $handle = fopen($file, 'r');
        fgetcsv($handle); // skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $uid = mysqli_real_escape_string($conn, $data[0]);
            $nama = mysqli_real_escape_string($conn, $data[1]);
            $kelompok = mysqli_real_escape_string($conn, $data[2]);
            $desa = mysqli_real_escape_string($conn, $data[3]);
            mysqli_query($conn, "INSERT INTO peserta_cai (uid, nama, kelompok, desa) VALUES ('$uid', '$nama', '$kelompok', '$desa')");
        }
        fclose($handle);
        header("Location: cai_peserta.php");
        exit();
    }
}

// Export ke Excel
if (isset($_GET['export'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data_peserta_cai.xls");
    echo "<table border='1'><tr><th>UID</th><th>Nama</th><th>Kelompok</th><th>Desa</th></tr>";
    $export_q = "SELECT uid, nama, kelompok, desa FROM peserta_cai WHERE 1=1";
    if ($desa_filter !== '') {
        $export_q .= " AND desa = '" . mysqli_real_escape_string($conn, $desa_filter) . "'";
    }
    $export_r = mysqli_query($conn, $export_q);
    while ($r = mysqli_fetch_assoc($export_r)) {
        echo "<tr><td>{$r['uid']}</td><td>{$r['nama']}</td><td>{$r['kelompok']}</td><td>{$r['desa']}</td></tr>";
    }
    echo "</table>";
    exit();
}

// Hapus peserta
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM peserta_cai WHERE id = $id");
    header("Location: cai_peserta.php");
    exit();
}

// Hapus semua
if (isset($_GET['hapus_semua']) && $_GET['hapus_semua'] === '1' && $desa_filter === '') {
    mysqli_query($conn, "DELETE FROM peserta_cai");
    header("Location: cai_peserta.php");
    exit();
}

// Hapus per desa
if (isset($_GET['hapus_desa'])) {
    $desa = mysqli_real_escape_string($conn, $_GET['hapus_desa']);
    mysqli_query($conn, "DELETE FROM peserta_cai WHERE desa = '$desa'");
    header("Location: cai_peserta.php");
    exit();
}

$query = "SELECT * FROM peserta_cai WHERE 1=1";
if ($desa_filter !== '') {
    $query .= " AND desa = '" . mysqli_real_escape_string($conn, $desa_filter) . "'";
}
$query .= " ORDER BY nama ASC";
$result = mysqli_query($conn, $query);

$total_desa = mysqli_query($conn, "SELECT desa, COUNT(*) as jumlah FROM peserta_cai GROUP BY desa ORDER BY desa ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Peserta CAI</title>
  <style>
    body { font-family: sans-serif; background: #f4f9ff; padding: 20px; }
    .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 12px; }
    h2 { color: #11698e; }
    table {
      width: 100%; border-collapse: collapse; margin-top: 20px; background: white;
    }
    th, td {
      border: 1px solid #ddd; padding: 10px; text-align: center;
    }
    th { background-color: #11698e; color: white; }

    .button-icons {
      display: flex;
      justify-content: center;
      gap: 10px;
    }
    .edit-btn {
      color: black; text-decoration: none; font-size: 18px;
    }
    .hapus {
      color: red; text-decoration: none; font-size: 18px;
    }
    .hapus:hover, .edit-btn:hover { text-decoration: underline; }

    .actions {
      margin: 20px 0;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }
    .actions a {
      padding: 8px 12px;
      border-radius: 6px;
      text-decoration: none;
      color: white;
    }
    .actions a[href*="hapus_semua"],
    .actions a[href*="hapus_desa"] {
      background: #e63946;
    }
    .actions a[href*="export"] {
      background: #00a9a5;
    }
    .actions a:hover { opacity: 0.9; }

    .back-home {
      display: inline-block;
      margin-bottom: 20px;
      background: #ccc;
      color: #000;
      padding: 6px 12px;
      border-radius: 8px;
      text-decoration: none;
    }

    .filter-form {
      margin: 10px 0;
    }
    .filter-form select,
    .filter-form button {
      padding: 6px;
      margin-right: 10px;
    }
  </style>
</head>
<body>
<div class="container">
  <a href="index.php" class="back-home">← Beranda</a>
  <h2>Data Peserta CAI</h2>

  <form class="filter-form" method="get">
    <label for="desa">Filter Desa:</label>
    <select name="desa" id="desa">
      <option value="">Semua Desa</option>
      <?php while ($d = mysqli_fetch_assoc($desa_list)): ?>
        <option value="<?= $d['desa'] ?>" <?= $desa_filter === $d['desa'] ? 'selected' : '' ?>><?= $d['desa'] ?></option>
      <?php endwhile; ?>
    </select>
    <button type="submit">Terapkan</button>
  </form>

  <form method="post" enctype="multipart/form-data" style="margin: 20px 0;">
    <label for="csv_file">Upload CSV:</label>
    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
    <button type="submit" name="upload_csv">Upload</button>
  </form>

  <table>
    <tr>
      <th>No</th><th>UID</th><th>Nama</th><th>Kelompok</th><th>Desa</th><th>Aksi</th>
    </tr>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['uid']) ?></td>
      <td><?= htmlspecialchars($row['nama']) ?></td>
      <td><?= htmlspecialchars($row['kelompok']) ?></td>
      <td><?= htmlspecialchars($row['desa']) ?></td>
      <td class="button-icons">
        <a class="edit-btn" href="cai_edit_peserta.php?id=<?= $row['id'] ?>" title="Edit">✏️</a>
        <a class="hapus" href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Hapus peserta ini?')">🗑️</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <div class="actions">
    <?php if ($desa_filter === ''): ?>
      <a href="?hapus_semua=1" onclick="return confirm('Yakin hapus semua data peserta?')">🗑️ Hapus Semua Peserta</a>
    <?php endif; ?>
    <?php if ($desa_filter): ?>
      <a href="?hapus_desa=<?= urlencode($desa_filter) ?>" onclick="return confirm('Yakin hapus semua peserta dari desa <?= htmlspecialchars($desa_filter) ?>?')">🗑️ Hapus Peserta Desa Ini</a>
    <?php endif; ?>
    <a href="?export=1<?= $desa_filter ? '&desa=' . urlencode($desa_filter) : '' ?>">⬇️ Export ke Excel</a>
  </div>

  <h3>Total Peserta per Desa</h3>
  <table>
    <tr><th>Desa</th><th>Jumlah Peserta</th></tr>
    <?php while ($row = mysqli_fetch_assoc($total_desa)): ?>
    <tr>
      <td><?= htmlspecialchars($row['desa']) ?></td>
      <td><?= $row['jumlah'] ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>
