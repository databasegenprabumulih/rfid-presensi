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

$pesan = "";
$peserta_info = "";
$debug_time = date('H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);
    $waktu = date('Y-m-d H:i:s');
    $tanggal = date('Y-m-d');
    $now = date('H:i:s');

    // Cek sesi aktif
    $sesi_query = mysqli_query($conn, "SELECT * FROM sesi_cai WHERE '$now' BETWEEN jam_mulai AND jam_selesai LIMIT 1");
    $sesi_data = mysqli_fetch_assoc($sesi_query);
    $nama_sesi = $sesi_data ? $sesi_data['nama_sesi'] : null;

    // Debug info waktu & sesi
    echo "<pre style='text-align:center;color:#888'>DEBUG: Sekarang jam $now<br>DEBUG: Sesi aktif = " . ($nama_sesi ?: 'TIDAK ADA') . "</pre>";

    // Cek apakah sudah absen pada sesi ini
    $cek = mysqli_query($conn, "SELECT 1 FROM presensi_cai WHERE uid = '$uid' AND DATE(waktu) = '$tanggal' AND sesi = '$nama_sesi'");
    if (mysqli_num_rows($cek) === 0 && $nama_sesi) {
        // Catat kehadiran
        mysqli_query($conn, "INSERT INTO presensi_cai (uid, waktu, sesi) VALUES ('$uid', '$waktu', '$nama_sesi')");

        // Ambil info peserta
        $peserta = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM peserta_cai WHERE uid = '$uid'"));
        if ($peserta) {
            $peserta_info = "<div class='info-item'><strong>" . htmlspecialchars($peserta['nama']) . "</strong></div>"
                           . "<div class='info-item'><strong>Kelompok:</strong> " . htmlspecialchars($peserta['kelompok']) . "</div>"
                           . "<div class='info-item'><strong>Desa:</strong> " . htmlspecialchars($peserta['desa']) . "</div>"
                           . "<div class='info-item'><strong>Sesi:</strong> $nama_sesi</div>"
                           . "<div class='info-item'><strong>Jam:</strong> " . date('H:i') . "</div>"
                           . "<div class='success'>✅ PRESENSI BERHASIL</div>";
        } else {
            $peserta_info = "<div class='info-item'>UID tidak dikenali.</div>";
        }
        $pesan = "";
    } else {
        $pesan = "Presensi sudah tercatat sebelumnya untuk sesi ini.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Presensi CAI</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: sans-serif;
      background: #f4f9ff;
    }
    .form-box {
      max-width: 500px;
      margin: 50px auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      text-align: center;
      position: relative;
    }
    .form-box h3 {
      color: #11698e;
      margin-bottom: 15px;
    }
    .form-box input {
      padding: 10px;
      width: 100%;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .form-box button {
      background: #11698e;
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      border-radius: 8px;
      cursor: pointer;
    }
    .form-box button:hover {
      background: #0e5a7a;
    }
    .message {
      text-align: center;
      margin-top: 15px;
      font-weight: bold;
      color: white;
      background-color: #dc3545;
      padding: 10px;
      border-radius: 8px;
    }
    .info-box {
      margin-top: 15px;
      background: #d4edda;
      padding: 15px;
      border-radius: 10px;
    }
    .info-box .info-item {
      color: #003366;
      font-size: 18px;
      font-weight: bold;
      margin: 6px 0;
    }
    .info-box .info-item:first-child {
      font-size: 24px;
      color: black;
    }
    .info-box .success {
      margin-top: 15px;
      color: white;
      background: green;
      padding: 8px;
      border-radius: 8px;
      font-weight: bold;
    }
    .home-link {
      position: absolute;
      top: 10px;
      left: 10px;
      background: #11698e;
      color: white;
      padding: 6px 12px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: bold;
    }
    .home-link:hover {
      background: #0e5a7a;
    }
  </style>
</head>
<body>
  <div class="form-box">
    <a href="index.php" class="home-link">← Beranda</a>
    <h3>Presensi CAI</h3>
    <form method="post">
      <input type="text" name="uid" placeholder="Tempelkan Kartu RFID / UID" required autofocus>
      <button type="submit">Presensi</button>
    </form>

    <?php if (!empty($pesan)): ?>
      <div class="message">❗ <?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <?php if (!empty($peserta_info)): ?>
      <div class="info-box">
        <?= $peserta_info ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
