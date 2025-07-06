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
  <meta charset="UTF-8">
  <meta name="google-site-verification" content="GNdisoTbeLD6VKPSS-PCjp3fQ4qGn0N-FvdyO2IS4RI" />
  <title>Dashboard Generus</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f1fdfd;
      display: flex;
    }

    .menu-toggle {
      font-size: 18px;
      padding: 8px 10px;
      background-color: #11698e;
      color: white;
      border: none;
      border-radius: 0 10px 10px 0;
      position: fixed;
      top: 50%;
      left: 180px;
      transform: translateY(-50%);
      z-index: 1100;
      cursor: pointer;
      transition: left 0.3s ease;
    }

    .sidebar {
      width: 180px;
      background-color: #11698e;
      color: white;
      padding: 15px;
      transition: left 0.3s ease;
      border-top-right-radius: 20px;
      border-bottom-right-radius: 20px;
      position: fixed;
      top: 0;
      left: 0;
      height: 100vh;
      z-index: 1000;
    }

    .sidebar.hidden {
      left: -200px;
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
      margin-left: 180px;
      padding: 40px;
      width: 100%;
      transition: margin-left 0.3s ease;
    }

    .main-content.full {
      margin-left: 0;
    }

    .title {
      font-family: 'Arial Black', sans-serif;
      font-size: 34px;
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
      display: flex;
      justify-content: center;
      align-items: center;
      height: 400px;
    }

    .icon-card {
      cursor: pointer;
      padding: 30px 40px;
      background: #11698e;
      color: white;
      border-radius: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
      transition: background 0.3s;
      text-align: center;
    }

    .icon-card:hover {
      background: #0a3e57;
    }

    .icon-card i {
      margin-bottom: 15px;
    }

    .icon-text {
      font-weight: bold;
      font-size: 18px;
    }

    .dropdown-sub {
      padding-left: 15px;
      display: none;
      flex-direction: column;
    }

    iframe {
      width: 100%;
      height: 90vh;
      border: none;
      border-radius: 20px;
      margin-top: 30px;
    }

    .kalender-link-box {
      margin-top: 50px;
      text-align: center;
    }

    .kalender-link-box p {
      font-size: 20px;
      color: #333;
      margin-bottom: 15px;
    }

    .kalender-link-box a {
      display: inline-block;
      padding: 14px 26px;
      font-size: 18px;
      background-color: #11698e;
      color: white;
      border-radius: 10px;
      text-decoration: none;
    }

    .kalender-link-box a:hover {
      background-color: #0a3e57;
    }
  </style>
</head>
<body>

<!-- Tombol menu -->
<button class="menu-toggle" id="toggleBtn" onclick="toggleSidebar()">
  <i class="fa fa-chevron-left"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="menu-title">MENU</div>
  <a href="#" onclick="showPage('beranda')"><i class="fa fa-home"></i> Beranda</a>
  <a href="#" onclick="showPage('presensi')"><i class="fa fa-clipboard"></i> Presensi</a>

  <a href="javascript:void(0);" onclick="toggleCaiMenu()" id="caiBtn"><i class="fa fa-leaf"></i> CAI <i class="fa fa-chevron-down" style="margin-left:auto;"></i></a>
  <div id="caiMenu" class="dropdown-sub" style="display: none;">
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="cai_peserta.php">📝 Data Peserta CAI</a>
      <a href="cai_presensi.php">📜 Presensi CAI</a>
      <a href="cai_atur_sesi.php">⏰ Atur Sesi</a>
      <a href="cai_rekap.php">📊 Rekap CAI</a>
    <?php else: ?>
      <a href="cai_presensi.php">📜 Presensi CAI</a>
      <a href="cai_rekap.php">📊 Rekap CAI</a>
    <?php endif; ?>
  </div>

  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="#" onclick="showPage('peserta')"><i class="fa fa-users"></i> Data Generus</a>
    <a href="#" onclick="showPage('harian')"><i class="fa fa-calendar-day"></i> Rekap Harian</a>
    <a href="#" onclick="showPage('total')"><i class="fa fa-calendar-check"></i> Total Hadir</a>
    <a href="#" onclick="showPage('kelola_wilayah')"><i class="fa fa-map-marker-alt"></i> Kelola Wilayah</a>
    <a href="#" onclick="showPage('setting')"><i class="fa fa-cog"></i> Setting</a>
  <?php endif; ?>

  <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Konten Utama -->
<div class="main-content" id="main">
  <div class="title">GENERUS PRABUMULIH</div>

  <div class="page active" id="beranda">
    <div class="welcome-box">
      <p>Selamat datang di sistem presensi Generus Prabumulih 💙</p>
      <p>Gunakan menu di sebelah kiri untuk mulai melakukan presensi, melihat data, dan mengelola peserta.</p>
    </div>

    <!-- Link Kalender Kegiatan -->
    <div class="kalender-link-box">
      <p>Cek jadwal di sini</p>
      <a href="kg_kalender_kegiatan.php">Kalender Kegiatan</a>
    </div>
  </div>

  <div class="page" id="presensi">
    <div class="icon-container">
      <div class="icon-card" onclick="window.location.href='form_presensi.php'">
        <i class="fa fa-id-card fa-5x"></i>
        <div class="icon-text">Halaman Presensi</div>
      </div>
    </div>
  </div>

  <div class="page" id="peserta">
    <iframe src="peserta.php"></iframe>
  </div>

  <div class="page" id="rekap">
    <iframe src="rekap_presensi.php"></iframe>
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
  const main = document.getElementById("main");
  const toggleBtn = document.getElementById("toggleBtn");
  const icon = toggleBtn.querySelector("i");

  sidebar.classList.toggle("hidden");
  main.classList.toggle("full");

  if (sidebar.classList.contains("hidden")) {
    icon.classList.remove("fa-chevron-left");
    icon.classList.add("fa-chevron-right");
    toggleBtn.style.left = "0";
  } else {
    icon.classList.remove("fa-chevron-right");
    icon.classList.add("fa-chevron-left");
    toggleBtn.style.left = "180px";
  }
}

function showPage(id) {
  document.querySelectorAll('.page').forEach(page => {
    page.classList.remove('active');
  });
  document.getElementById(id).classList.add('active');
}

function toggleCaiMenu() {
  const menu = document.getElementById('caiMenu');
  menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}
</script>

</body>
</html>
