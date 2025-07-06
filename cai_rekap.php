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

// Set zona waktu ke WIB
date_default_timezone_set('Asia/Jakarta');

// Hapus presensi per peserta
if (isset($_GET['hapus_uid'])) {
    $uid = mysqli_real_escape_string($conn, $_GET['hapus_uid']);
    if ($uid === 'ALL') {
        mysqli_query($conn, "DELETE FROM presensi_cai");
    } else {
        mysqli_query($conn, "DELETE FROM presensi_cai WHERE uid = '$uid'");
    }
    header("Location: cai_rekap.php");
    exit();
}

// Hapus presensi per sesi
if (isset($_GET['hapus_sesi'])) {
    $sesi = mysqli_real_escape_string($conn, $_GET['hapus_sesi']);
    mysqli_query($conn, "DELETE FROM presensi_cai WHERE sesi = '$sesi'");
    header("Location: cai_rekap.php");
    exit();
}

// Export Excel
if (isset($_GET['export'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=rekap_presensi_cai.xls");
    echo "<table border='1'>";
    echo "<tr><th>Nama</th><th>Desa</th><th>Waktu</th><th>Sesi</th></tr>";
    $query_export = "SELECT p.nama, p.desa, pr.waktu, pr.sesi FROM presensi_cai pr JOIN peserta_cai p ON pr.uid = p.uid ORDER BY pr.waktu DESC";
    $res_export = mysqli_query($conn, $query_export);
    while ($r = mysqli_fetch_assoc($res_export)) {
        echo "<tr><td>{$r['nama']}</td><td>{$r['desa']}</td><td>{$r['waktu']}</td><td>{$r['sesi']}</td></tr>";
    }
    echo "</table>";
    exit();
}

$sesi_filter = $_GET['sesi'] ?? '';
$tanggal_filter = $_GET['tanggal'] ?? '';
$desa_filter = $_GET['desa'] ?? '';

$sesi_list = mysqli_query($conn, "SELECT DISTINCT sesi FROM presensi_cai WHERE sesi IS NOT NULL ORDER BY sesi ASC");
$desa_list = mysqli_query($conn, "SELECT DISTINCT desa FROM peserta_cai WHERE desa IS NOT NULL ORDER BY desa ASC");

$total_peserta = mysqli_num_rows(mysqli_query($conn, "SELECT uid FROM peserta_cai"));
$statistik_kehadiran_persesi = mysqli_query($conn, "SELECT sesi, COUNT(DISTINCT uid) as hadir FROM presensi_cai GROUP BY sesi");
$sesi_all = mysqli_query($conn, "SELECT nama_sesi FROM sesi_cai");

// Query data presensi peserta
$query = "SELECT p.uid, p.nama, p.desa, pr.waktu, pr.sesi FROM presensi_cai pr
          JOIN peserta_cai p ON pr.uid = p.uid WHERE 1=1";
if ($sesi_filter !== '') {
    $query .= " AND pr.sesi = '" . mysqli_real_escape_string($conn, $sesi_filter) . "'";
}
if ($tanggal_filter !== '') {
    $query .= " AND DATE(pr.waktu) = '" . mysqli_real_escape_string($conn, $tanggal_filter) . "'";
}
if ($desa_filter !== '') {
    $query .= " AND p.desa = '" . mysqli_real_escape_string($conn, $desa_filter) . "'";
}
$query .= " ORDER BY pr.waktu DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Presensi CAI</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: sans-serif; background: #f4f9ff; }
    .container { max-width: 1000px; margin: auto; padding: 20px; background: white; border-radius: 12px; }
    h2 { color: #11698e; text-align: center; }
    .home-link {
      display: inline-block;
      margin-bottom: 20px;
      background: #11698e;
      color: white;
      padding: 6px 12px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }
    .home-link:hover { background: #0e5a7a; }
    canvas { max-width: 350px !important; margin: 20px auto; display: block; }
    .hapus-buttons { margin: 20px 0; text-align: center; }
    .hapus-buttons a {
      margin: 5px;
      display: inline-block;
      background: #e63946;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    .hapus-buttons a:hover { background: #c92a35; }
    form.filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
      justify-content: center;
    }
    form.filter-form select, form.filter-form input[type="date"] {
      padding: 6px 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    form.filter-form button {
      background: #11698e;
      color: white;
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .export-button {
      text-align: center;
      margin-bottom: 20px;
    }
    .export-button a {
      background: #00a9a5;
      color: white;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    .export-button a:hover {
      background: #088;
    }
    table {
      width: 100%;
      margin-top: 30px;
      border-collapse: collapse;
      background: white;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: center;
    }
    th {
      background-color: #11698e;
      color: white;
    }
    a.hapus {
      color: red;
      text-decoration: none;
    }
    a.hapus:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.php" class="home-link">← Beranda</a>

    <form class="filter-form" method="get">
      <select name="sesi">
        <option value="">Semua Sesi</option>
        <?php mysqli_data_seek($sesi_list, 0); while ($s = mysqli_fetch_assoc($sesi_list)): ?>
        <option value="<?= $s['sesi'] ?>" <?= $sesi_filter === $s['sesi'] ? 'selected' : '' ?>><?= $s['sesi'] ?></option>
        <?php endwhile; ?>
      </select>
      <select name="desa">
        <option value="">Semua Desa</option>
        <?php mysqli_data_seek($desa_list, 0); while ($d = mysqli_fetch_assoc($desa_list)): ?>
        <option value="<?= $d['desa'] ?>" <?= $desa_filter === $d['desa'] ? 'selected' : '' ?>><?= $d['desa'] ?></option>
        <?php endwhile; ?>
      </select>
      <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_filter) ?>">
      <button type="submit">Terapkan Filter</button>
    </form>

    <div class="export-button">
      <a href="?export=1">⬇️ Export ke Excel</a>
    </div>

    <h2>Data Kehadiran Peserta</h2>
    <table>
      <tr><th>Nama</th><th>Desa</th><th>Waktu</th><th>Sesi</th><th>Hapus</th></tr>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['desa']) ?></td>
        <td><?= $row['waktu'] ?></td>
        <td><?= $row['sesi'] ?></td>
        <td>
          <?php if ($_SESSION['role'] === 'admin'): ?>
            <a class="hapus" href="?hapus_uid=<?= $row['uid'] ?>" onclick="return confirm('Hapus presensi peserta ini?')">🗑️</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>

    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="hapus-buttons">
      <a href="?hapus_uid=ALL" onclick="return confirm('Hapus semua presensi peserta?')">🗑️ Hapus Semua Presensi</a>
      <?php mysqli_data_seek($sesi_list, 0); while ($s = mysqli_fetch_assoc($sesi_list)): ?>
        <a href="?hapus_sesi=<?= urlencode($s['sesi']) ?>" onclick="return confirm('Hapus presensi untuk sesi <?= $s['sesi'] ?>?')">🗑️ Hapus Sesi <?= $s['sesi'] ?></a>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>

    <h2>Grafik Kehadiran vs Total Peserta per Sesi</h2>
    <canvas id="compareSesi"></canvas>

    <h2>Grafik Kehadiran Peserta per Sesi</h2>
    <canvas id="kehadiranSesi"></canvas>
  </div>

  <script>
    const sesiLabels = [
      <?php mysqli_data_seek($statistik_kehadiran_persesi, 0); while ($row = mysqli_fetch_assoc($statistik_kehadiran_persesi)) echo "'{$row['sesi']}',"; ?>
    ];
    const hadirData = [
      <?php mysqli_data_seek($statistik_kehadiran_persesi, 0); while ($row = mysqli_fetch_assoc($statistik_kehadiran_persesi)) echo "{$row['hadir']},"; ?>
    ];
    const totalData = Array(sesiLabels.length).fill(<?= $total_peserta ?>);

    new Chart(document.getElementById('compareSesi'), {
      type: 'bar',
      data: {
        labels: sesiLabels,
        datasets: [
          {
            label: 'Total Peserta',
            data: totalData,
            backgroundColor: '#ddd'
          },
          {
            label: 'Hadir',
            data: hadirData,
            backgroundColor: '#11698e'
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });

    new Chart(document.getElementById('kehadiranSesi'), {
      type: 'pie',
      data: {
        labels: sesiLabels,
        datasets: [{
          data: hadirData,
          backgroundColor: ['#11698e', '#00a9a5', '#4bb3fd', '#ffcb77', '#f29e4c']
        }]
      }
    });
  </script>
</body>
</html>
