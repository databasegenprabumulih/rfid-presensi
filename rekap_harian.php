<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah admin
$is_admin = ($_SESSION['role'] === 'admin');

// Koneksi database
$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$query = "SELECT d.id, p.nama_generus, p.kelompok, p.desa, d.waktu FROM data_presensi d 
          JOIN peserta p ON d.uid = p.uid 
          WHERE DATE(d.waktu) = '$tanggal' 
          ORDER BY d.waktu ASC";
$result = mysqli_query($conn, $query);

if (!$is_admin) {
    $tanggal = date('Y-m-d');
    $query = "SELECT d.id, p.nama_generus, p.kelompok, p.desa, d.waktu FROM data_presensi d 
              JOIN peserta p ON d.uid = p.uid 
              WHERE DATE(d.waktu) = '$tanggal' 
              ORDER BY d.waktu ASC";
    $result = mysqli_query($conn, $query);
}

$notes = [];
$notes_query = mysqli_query($conn, "SELECT * FROM catatan_kegiatan");
while ($n = mysqli_fetch_assoc($notes_query)) {
    $notes[$n['tanggal']] = $n['catatan'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $tanggal_post = $_POST['tanggal'];
    $catatan_post = $_POST['catatan'];
    $tanggal_safe = mysqli_real_escape_string($conn, $tanggal_post);
    $catatan_safe = mysqli_real_escape_string($conn, $catatan_post);
    mysqli_query($conn, "INSERT INTO catatan_kegiatan (tanggal, catatan) VALUES ('$tanggal_safe', '$catatan_safe') ON DUPLICATE KEY UPDATE catatan = '$catatan_safe'");
    header("Location: ?tanggal=$tanggal_post");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekap Harian</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8fcff;
      padding: 20px;
    }
    h2 {
      color: #11698e;
      text-align: center;
      margin-bottom: 10px;
    }
    #calendar {
      max-width: 600px;
      margin: 0 auto;
      background: white;
      padding: 10px;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .info {
      text-align: center;
      margin-top: 30px;
      font-size: 16px;
      color: #444;
    }
    form.catatan-form {
      max-width: 400px;
      margin: 10px auto 30px auto;
      display: flex;
      gap: 10px;
      justify-content: center;
    }
    form.catatan-form input[type="text"] {
      flex: 1;
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }
    form.catatan-form button {
      padding: 6px 12px;
      background-color: #11698e;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    form.catatan-form button:hover {
      background-color: #0e5a7a;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #ffffff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    th, td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #11698e;
      color: white;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
    .no-data {
      text-align: center;
      padding: 20px;
      color: #999;
    }
    .btn-group {
      text-align: center;
      margin: 20px 0;
    }
    .btn-group a {
      display: inline-block;
      background: #11698e;
      color: white;
      padding: 10px 15px;
      border-radius: 10px;
      text-decoration: none;
      margin: 0 10px;
    }
    .btn-group a.red {
      background: red;
    }
    .btn-group a.red:hover {
      background: darkred;
    }
    .btn-group a:hover {
      opacity: 0.9;
    }
    .delete-icon {
      color: red;
      text-decoration: none;
      font-weight: bold;
    }
    .delete-icon:hover {
      color: darkred;
    }
  </style>
</head>
<body>
  <h2>Rekap Harian</h2>
  <div id="calendar"></div>
  <div class="info">
    Menampilkan data presensi untuk tanggal: <strong><?= date('d M Y', strtotime($tanggal)) ?></strong><br>
    <em><?= $notes[$tanggal] ?? '' ?></em>
  </div>

  <?php if ($is_admin): ?>
    <form class="catatan-form" method="post">
      <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
      <input type="text" name="catatan" placeholder="Catatan kegiatan..." value="<?= htmlspecialchars($notes[$tanggal] ?? '') ?>">
      <button type="submit">Simpan</button>
    </form>
  <?php endif; ?>

  <?php if ($is_admin && mysqli_num_rows($result) > 0): ?>
  <div class="btn-group">
    <a href="?tanggal=<?= $tanggal ?>&hapus_semua=<?= $tanggal ?>" class="red" onclick="return confirm('Yakin ingin menghapus semua data presensi pada tanggal ini?')">
      🗑️ Hapus Semua Presensi
    </a>
    <a href="?tanggal=<?= $tanggal ?>&export=<?= $tanggal ?>">⬇️ Export ke Excel</a>
  </div>
  <?php endif; ?>
  <?php if (mysqli_num_rows($result) > 0): ?>
  <table>
    <tr>
      <th>Nama Generus</th>
      <th>Kelompok</th>
      <th>Desa</th>
      <th>Waktu Presensi</th>
      <?php if ($is_admin): ?><th>🗑️</th><?php endif; ?>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= htmlspecialchars($row['nama_generus']) ?></td>
      <td><?= htmlspecialchars($row['kelompok']) ?></td>
      <td><?= htmlspecialchars($row['desa']) ?></td>
      <td><?= date('H:i:s', strtotime($row['waktu'])) ?></td>
      <?php if ($is_admin): ?>
        <td><a href="?tanggal=<?= $tanggal ?>&hapus_id=<?= $row['id'] ?>" class="delete-icon" onclick="return confirm('Hapus data presensi ini?')">🗑️</a></td>
      <?php endif; ?>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php else: ?>
    <div class="no-data">Belum ada data kehadiran pada tanggal ini.</div>
  <?php endif; ?>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      var calendar = new window.FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: ''
        },
        events: [
          <?php
          $start = date('Y-m-01');
          $end = date('Y-m-t');
          $period = new DatePeriod(
              new DateTime($start),
              new DateInterval('P1D'),
              (new DateTime($end))->modify('+1 day')
          );
          foreach ($period as $date) {
              $tgl = $date->format('Y-m-d');
              $has_data = mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM data_presensi WHERE DATE(waktu) = '$tgl'")) > 0;
              $catatan = $notes[$tgl] ?? '';
              if ($has_data || $catatan) {
                  $title = $has_data ? 'Presensi' : '';
                  $note = $catatan ? ' - ' . addslashes($catatan) : '';
                  echo "{ title: '$title$note', date: '$tgl' },";
              }
          }
          ?>
        ],
        dateClick: function(info) {
          window.location.href = '?tanggal=' + info.dateStr;
        }
      });
      calendar.render();
    });
  </script>
</body>
</html>
