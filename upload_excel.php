<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require 'vendor/autoload.php'; // PHPSpreadsheet

$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Koneksi database gagal: " . mysqli_connect_error());

$success_count = 0;
$duplicate_uids = [];

if (isset($_POST['upload'])) {
    $file = $_FILES['excel']['tmp_name'];
    if ($file) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Lewati baris header

            $uid = trim($row[0]);
            $nama_generus = trim($row[1]);
            $kelompok = trim($row[2]);
            $desa = trim($row[3]);

            if ($uid === "" || $nama_generus === "" || $kelompok === "" || $desa === "") continue;

            // Cek UID apakah sudah ada
            $cek = mysqli_query($conn, "SELECT 1 FROM peserta WHERE uid = '$uid' LIMIT 1");
            if (mysqli_num_rows($cek) > 0) {
                $duplicate_uids[] = $uid;
                continue;
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO peserta (uid, nama_generus, kelompok, desa) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $uid, $nama_generus, $kelompok, $desa);
            if (mysqli_stmt_execute($stmt)) $success_count++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Excel Peserta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 30px;
        }
        .box {
            max-width: 500px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type=file] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }
        button {
            background: #11698e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0d536b;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #11698e;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .message {
            margin-top: 20px;
            background: #e6f7f7;
            border-left: 4px solid #11698e;
            padding: 10px 15px;
            border-radius: 6px;
        }
        .message .warn {
            color: #c00;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>Upload Data Excel</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="excel" accept=".xlsx,.xls" required>
        <button type="submit" name="upload">Upload</button>
    </form>

    <a href="peserta.php" class="back-link">&larr; Kembali ke Data Peserta</a>

    <?php if (isset($_POST['upload'])): ?>
        <div class="message">
            ✅ <?= $success_count ?> data berhasil diunggah.
            <?php if (!empty($duplicate_uids)): ?>
                <div class="warn">⚠️ <?= count($duplicate_uids) ?> UID duplikat tidak dimasukkan:
                    <?= implode(', ', array_slice($duplicate_uids, 0, 10)) ?>
                    <?= count($duplicate_uids) > 10 ? '...' : '' ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
