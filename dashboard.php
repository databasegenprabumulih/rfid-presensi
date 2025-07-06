<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard Generus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f1fdfd;
      display: flex;
    }

    .menu-toggle {
      font-size: 20px;
      padding: 10px 12px;
      background-color: #11698e;
      color: white;
      border: none;
      border-radius: 0 10px 10px 0;
      position: fixed;
      top: 20px;
      left: 0;
      z-index: 1100;
      cursor: pointer;
      display: none;
    }

    .menu-toggle.visible {
      display: block;
    }

    .sidebar {
      width: 200px;
      background-color: #11698e;
      color: white;
      padding: 15px;
      transition: transform 0.3s ease;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 1000;
      transform: translateX(0);
    }

    .sidebar.hidden {
      transform: translateX(-220px);
    }

    .sidebar .menu-title {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 15px;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      margin: 6px 0;
      color: white;
      background: #0b4f6c;
      text-decoration: none;
      border-radius: 10px;
      font-size: 14px;
      transition: background 0.3s;
    }

    .sidebar a:hover {
      background: #0a3e57;
    }

    .main-content {
      margin-left: 200px;
      padding: 40px 20px;
      width: 100%;
      transition: margin-left 0.3s ease;
    }

    .main-content.full {
      margin-left: 0;
    }

    .title {
      font-family: 'Arial Black', sans-serif;
      font-size: 32px;
      color: #000;
      text-align: center;
      margin-bottom: 30px;
    }

    .page {
      display: none;
      animation: fadeIn 0.3s ease;
    }

    .page.active {
      display: block;
    }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(20px);}
      to {opacity: 1; transform: translateY(0);}
    }

    .welcome-box {
      text-align: center;
      font-size: 18px;
      padding: 30px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      color: #11698e;
    }

    .icon-container {
      text-align: center;
    }

    .icon-card {
      cursor: pointer;
      display: inline-block;
      padding: 30px 40px;
      background: #11698e;
      color: white;
      border-radius: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
      transition: background 0.3s;
    }

    .icon-card:hover {
      background: #0a3e57;
    }

    .icon-text {
      font-weight: bold;
      font-size: 18px;
    }

    iframe {
      width: 100%;
      height: 90vh;
      border-radius: 20px;
      border: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-220px);
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .menu-toggle {
        display: block;
      }
    }
  </style>
</head>
<body>

<!-- Tombol Toggle -->
<button class="menu-toggle visible" id="toggleBtn" onclick="toggleSidebar()">
  <i class="fa fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar hidden" id="sidebar">
  <div class="menu-title">MENU</div>
  <a href="#" onclick="showPage('beranda')"><i class="fa fa-home"></i> Beranda</a>
  <a href="#" onclick="showPage('presensi')"><i class="fa fa-clipboard"></i> Presensi</a>

  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="#" onclick="showPage('peserta')"><i class="fa fa-users"></i> Data Generus</a>
    <a href="#" onclick="showPage('harian')"><i class="fa fa-calendar-day"></i> Rekap Harian</a>
    <a href="#" onclick="showPage('total')"><i class="fa fa-calendar-check"></i> Total Hadir</a>
    <a href="#" onclick="showPage('kelola_wilayah')"><i class="fa fa-map-marker-alt"></i> Kelola Wilayah</a>
    <a href="#" onclick="showPage('setting')"><i class="fa fa-cog"></i> Setting</a>
  <?php endif; ?>

  <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Konten -->
<div class="main-content full" id="main">
  <div class="title">GENERUS PRABUMULIH</div>

  <div class="page active" id="beranda">
    <div class="welcome-box">
      <p>Selamat datang di sistem presensi Generus Prabumulih 💙</p>
      <p>Gunakan menu di sebelah kiri untuk mulai melakukan presensi, melihat data, dan mengelola peserta.</p>
    </div>
  </div>

  <div class="page" id="presensi">
    <div class="icon-container">
      <div class="icon-card" onclick="window.location.href='form_presensi.php'">
        <i class="fa fa-id-card fa-5x"></i>
        <div class="icon-text">Menuju Halaman Presensi</div>
      </div>
    </div>
  </div>

  <div class="page" id="peserta">
    <iframe src="peserta.php"></iframe>
  </div>

  <div class="page" id="harian">
    <iframe src="rekap_harian.php"></iframe>
  </div>

  <div class="page" id="total">
    <iframe src="rekap_total.php"></iframe>
  </div>

  <div class="page" id="kelola_wilayah">
    <iframe src="kelola_wilayah.php"></iframe>
  </div>

  <div class="page" id="setting">
    <iframe src="setting.php"></iframe>
  </div>
</div>

<script>
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  sidebar.classList.toggle("hidden");
  sidebar.classList.toggle("active");
}

function showPage(id) {
  document.querySelectorAll('.page').forEach(page => page.classList.remove('active'));
  document.getElementById(id).classList.add('active');

  // Auto close sidebar on mobile
  if (window.innerWidth <= 768) {
    document.getElementById("sidebar").classList.add("hidden");
  }
}
</script>

</body>
</html>
