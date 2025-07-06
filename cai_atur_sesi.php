<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_sesi = $_POST['nama_sesi'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    $nama_sesi = mysqli_real_escape_string($conn, $nama_sesi);
    $jam_mulai = mysqli_real_escape_string($conn, $jam_mulai);
    $jam_selesai = mysqli_real_escape_string($conn, $jam_selesai);

    mysqli_query($conn, "INSERT INTO sesi_cai (nama_sesi, jam_mulai, jam_selesai) VALUES ('$nama_sesi', '$jam_mulai', '$jam_selesai')");
    header("Location: cai_atur_sesi.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM sesi_cai WHERE id = $id");
    header("Location: cai_atur_sesi.php");
    exit();
}

$sesi_result = mysqli_query($conn, "SELECT * FROM sesi_cai ORDER BY jam_mulai ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Atur Sesi CAI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-box {
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .form-box h3 {
            text-align: center;
            color: #11698e;
            margin-bottom: 15px;
        }
        .form-box input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .form-box button {
            background: #11698e;
            color: white;
            padding: 10px;
            width: 100%;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .form-box button:hover {
            background: #0e5a7a;
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
    <a href="index.php" class="home-link">← Beranda</a>
    <div class="container">
        <h2>Atur Sesi CAI</h2>

        <div class="form-box">
            <h3>Tambah Sesi</h3>
            <form method="post">
                <input type="text" name="nama_sesi" placeholder="Nama Sesi (contoh: Subuh)" required>
                <input type="time" name="jam_mulai" required>
                <input type="time" name="jam_selesai" required>
                <button type="submit">Simpan Sesi</button>
            </form>
        </div>

        <table>
            <tr>
                <th>Nama Sesi</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Hapus</th>
            </tr>
            <?php while ($sesi = mysqli_fetch_assoc($sesi_result)): ?>
                <tr>
                    <td><?= htmlspecialchars($sesi['nama_sesi']) ?></td>
                    <td><?= htmlspecialchars($sesi['jam_mulai']) ?></td>
                    <td><?= htmlspecialchars($sesi['jam_selesai']) ?></td>
                    <td><a href="?hapus=<?= $sesi['id'] ?>" class="hapus" onclick="return confirm('Yakin hapus sesi ini?')">🗑️</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
