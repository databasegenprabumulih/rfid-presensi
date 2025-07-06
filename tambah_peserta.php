<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    echo "<h2 style='text-align:center; color:red;'>Akses ditolak. Halaman ini hanya untuk admin.</h2>";
    exit();
}

// Koneksi database
$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data desa dan kelompok
$desa_list = [];
$kelompok_list = [];

$res_desa = mysqli_query($conn, "SELECT * FROM desa ORDER BY nama_desa ASC");
while ($row = mysqli_fetch_assoc($res_desa)) {
    $desa_list[] = $row;
}

$res_kelompok = mysqli_query($conn, "SELECT * FROM kelompok ORDER BY nama_kelompok ASC");
while ($row = mysqli_fetch_assoc($res_kelompok)) {
    $kelompok_list[] = $row;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uid = trim($_POST['uid']);
    $nama_generus = trim($_POST['nama_generus']);
    $kelompok = trim($_POST['kelompok']);
    $desa_id = intval($_POST['desa_id']);

    // Ambil nama desa dari ID
    $desa_q = mysqli_query($conn, "SELECT nama_desa FROM desa WHERE id = '$desa_id' LIMIT 1");
    $desa_data = mysqli_fetch_assoc($desa_q);
    $desa = $desa_data ? $desa_data['nama_desa'] : '';

    if ($uid === "" || $nama_generus === "" || $kelompok === "" || $desa_id === 0) {
        $error = "Semua field harus diisi.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO peserta (uid, nama_generus, kelompok, desa) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $uid, $nama_generus, $kelompok, $desa);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: peserta.php");
            exit();
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Tambah Peserta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f9f9f9;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type=text], select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #11698e;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0d536b;
        }
        .error {
            color: red;
            margin-top: 15px;
            font-weight: bold;
        }
        a.back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #11698e;
        }
        a.back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="peserta.php" class="back">&larr; Kembali ke Data Peserta</a>
    <form method="POST" action="">
        <h2>Tambah Peserta Baru</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label for="uid">UID</label>
        <input type="text" id="uid" name="uid" required>

        <label for="nama_generus">Nama Generus</label>
        <input type="text" id="nama_generus" name="nama_generus" required>

        <label for="desa">Desa</label>
        <select id="desa" name="desa_id" onchange="filterKelompokByDesa()" required>
            <option value="">-- Pilih Desa --</option>
            <?php foreach ($desa_list as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nama_desa']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="kelompok">Kelompok</label>
        <select id="kelompok" name="kelompok" required>
            <option value="">-- Pilih Kelompok --</option>
        </select>

        <input type="hidden" id="allKelompok" value='<?= json_encode($kelompok_list) ?>'>

        <button type="submit">Simpan</button>
    </form>

    <script>
        function filterKelompokByDesa() {
            const desaId = document.getElementById("desa").value;
            const allKelompok = JSON.parse(document.getElementById("allKelompok").value);
            const kelompokSelect = document.getElementById("kelompok");

            kelompokSelect.innerHTML = '<option value="">-- Pilih Kelompok --</option>';

            allKelompok.forEach(function(k) {
                if (k.desa_id == desaId) {
                    const opt = document.createElement("option");
                    opt.value = k.nama_kelompok;
                    opt.text = k.nama_kelompok;
                    kelompokSelect.appendChild(opt);
                }
            });
        }
    </script>
</body>
</html>
