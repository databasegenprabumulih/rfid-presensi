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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$peserta = mysqli_query($conn, "SELECT * FROM peserta_cai WHERE id = $id");
if (!$peserta || mysqli_num_rows($peserta) === 0) {
    echo "<p>Peserta tidak ditemukan.</p>";
    exit();
}
$data = mysqli_fetch_assoc($peserta);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelompok = mysqli_real_escape_string($conn, $_POST['kelompok']);
    $desa = mysqli_real_escape_string($conn, $_POST['desa']);

    mysqli_query($conn, "UPDATE peserta_cai SET uid='$uid', nama='$nama', kelompok='$kelompok', desa='$desa' WHERE id=$id");
    header("Location: cai_peserta.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Peserta</title>
  <style>
    body {
      font-family: sans-serif;
      background: #f4f9ff;
      padding: 20px;
    }
    .form-container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #11698e;
    }
    input[type="text"], button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      background: #11698e;
      color: white;
      border: none;
      cursor: pointer;
    }
    button:hover {
      background: #0e5a7a;
    }
    a.back {
      display: inline-block;
      margin-bottom: 20px;
      color: #11698e;
      text-decoration: none;
    }
    a.back:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <a class="back" href="cai_peserta.php">← Kembali</a>
    <h2>Edit Peserta</h2>
    <form method="post">
      <label>UID:</label>
      <input type="text" name="uid" value="<?= htmlspecialchars($data['uid']) ?>" required>

      <label>Nama:</label>
      <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>

      <label>Kelompok:</label>
      <input type="text" name="kelompok" value="<?= htmlspecialchars($data['kelompok']) ?>" required>

      <label>Desa:</label>
      <input type="text" name="desa" value="<?= htmlspecialchars($data['desa']) ?>" required>

      <button type="submit">Simpan Perubahan</button>
    </form>
  </div>
</body>
</html>
