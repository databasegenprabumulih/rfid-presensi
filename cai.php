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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cinta Alam Indonesia</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .cai-container {
      max-width: 800px;
      margin: 30px auto;
      background: white;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      text-align: center;
    }
    .cai-title {
      color: #11698e;
      font-size: 28px;
      margin-bottom: 20px;
      font-weight: bold;
    }
    .cai-button {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 12px;
      background: #11698e;
      color: white;
      text-decoration: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: bold;
      transition: background 0.3s;
    }
    .cai-button:hover {
      background: #0e5a7a;
    }
    @media (max-width: 600px) {
      .cai-title {
        font-size: 22px;
      }
      .cai-container {
        padding: 20px;
      }
      .cai-button {
        font-size: 15px;
        padding: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="cai-container">
    <div class="cai-title">CINTA ALAM INDONESIA</div>
    <a href="cai_peserta.php" class="cai-button">📝 Input Data Peserta</a>
    <a href="cai_presensi.php" class="cai-button">📋 Masuk Presensi</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="cai_sesi.php" class="cai-button">⏰ Atur Sesi Presensi</a>
    <?php endif; ?>
  </div>
</body>
</html>
