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
  <title>Halaman Presensi</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8fcff;
      margin: 0;
      padding: 40px;
      text-align: center;
    }
    .container {
      background: white;
      max-width: 500px;
      margin: auto;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    h2 {
      color: #11698e;
      margin-bottom: 20px;
    }
    a.btn {
      display: inline-block;
      background-color: #11698e;
      color: white;
      padding: 12px 24px;
      text-decoration: none;
      border-radius: 8px;
      font-size: 16px;
      transition: background 0.3s;
    }
    a.btn:hover {
      background-color: #0e5a7a;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Halaman Presensi</h2>
    <p>Silakan klik tombol di bawah ini untuk melakukan presensi.</p>
    <a href="form_presensi.php" class="btn">Menuju Form Presensi</a>
  </div>
</body>
</html>
