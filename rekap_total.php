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

$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$desa = isset($_GET['desa']) ? $_GET['desa'] : '';
$kelompok = isset($_GET['kelompok']) ? $_GET['kelompok'] : '';

$whereClauses = [];
$whereClauses[] = "MONTH(d.waktu) = '$bulan'";
$whereClauses[] = "YEAR(d.waktu) = '$tahun'";
if ($desa !== '') {
    $desa_safe = mysqli_real_escape_string($conn, $desa);
    $whereClauses[] = "p.desa = '$desa_safe'";
}
if ($kelompok !== '') {
    $kelompok_safe = mysqli_real_escape_string($conn, $kelompok);
    $whereClauses[] = "p.kelompok = '$kelompok_safe'";
}
$whereSQL = implode(' AND ', $whereClauses);

$query = "
    SELECT 
        p.uid,
        p.nama_generus, 
        p.kelompok, 
        p.desa, 
        COUNT(d.uid) AS total_hadir
    FROM peserta p
    LEFT JOIN data_presensi d ON p.uid = d.uid AND $whereSQL
    GROUP BY p.uid
    ORDER BY total_hadir DESC
";

$result = mysqli_query($conn, $query);

$desaListResult = mysqli_query($conn, "SELECT DISTINCT desa FROM peserta ORDER BY desa ASC");
$desaList = [];
while ($row = mysqli_fetch_assoc($desaListResult)) {
    $desaList[] = $row['desa'];
}

$kelompokListResult = mysqli_query($conn, "SELECT DISTINCT kelompok FROM peserta ORDER BY kelompok ASC");
$kelompokList = [];
while ($row = mysqli_fetch_assoc($kelompokListResult)) {
    $kelompokList[] = $row['kelompok'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Total Kehadiran Generus</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7fbfd;
            margin: 40px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
        }
        h2 {
            color: #11698e;
            text-align: center;
            margin-bottom: 25px;
        }
        form.filter-form {
            margin-bottom: 30px;
        }
        .filter-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            justify-content: center;
        }
        .filter-row label {
            min-width: 70px;
            font-weight: 600;
            color: #11698e;
            align-self: center;
        }
        select {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1.5px solid #11698e;
            font-size: 15px;
            transition: border-color 0.3s ease;
        }
        select:hover, select:focus {
            border-color: #0b4f6c;
            outline: none;
        }
        button.filter-btn {
            width: 100%;
            padding: 12px 0;
            font-size: 16px;
            font-weight: 700;
            background-color: #11698e;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button.filter-btn:hover {
            background-color: #0b4f6c;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            font-size: 15px;
        }
        thead th {
            background-color: #11698e;
            color: white;
            padding: 12px 15px;
            text-align: left;
            border-radius: 8px 8px 0 0;
        }
        tbody tr {
            background-color: #f9fbfc;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
            transition: background-color 0.3s ease;
        }
        tbody tr:hover {
            background-color: #e3f2fd;
        }
        tbody td {
            padding: 12px 15px;
        }
        a.delete-btn {
            color: #e74c3c;
            font-size: 20px;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        a.delete-btn:hover {
            color: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Total Kehadiran Generus</h2>

    <form class="filter-form" method="GET" action="">
        <div class="filter-row">
            <label for="kelompok">Kelompok:</label>
            <select name="kelompok" id="kelompok">
                <option value="">-- Semua Kelompok --</option>
                <?php
                foreach ($kelompokList as $k) {
                    $selected = ($kelompok === $k) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($k) . "' $selected>" . htmlspecialchars($k) . "</option>";
                }
                ?>
            </select>

            <label for="desa">Desa:</label>
            <select name="desa" id="desa">
                <option value="">-- Semua Desa --</option>
                <?php
                foreach ($desaList as $d) {
                    $selected = ($desa === $d) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($d) . "' $selected>" . htmlspecialchars($d) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="filter-row">
            <label for="bulan">Bulan:</label>
            <select name="bulan" id="bulan" required>
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $val = sprintf("%02d", $m);
                    $selected = ($bulan == $val) ? 'selected' : '';
                    $monthName = date('F', mktime(0, 0, 0, $m, 10));
                    echo "<option value='$val' $selected>$monthName</option>";
                }
                ?>
            </select>

            <label for="tahun">Tahun:</label>
            <select name="tahun" id="tahun" required>
                <?php
                $yearStart = 2023;
                $yearEnd = date('Y');
                for ($y = $yearStart; $y <= $yearEnd; $y++) {
                    $selected = ($tahun == $y) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </div>

        <div class="filter-row" style="justify-content:center;">
            <button type="submit" class="filter-btn">Filter</button>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Generus</th>
                <th>Kelompok</th>
                <th>Desa</th>
                <th>Total Hadir</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $uid = htmlspecialchars($row['uid']);
            $nama = htmlspecialchars($row['nama_generus']);
            $kelompokData = htmlspecialchars($row['kelompok']);
            $desaData = htmlspecialchars($row['desa']);
            $total = $row['total_hadir'];

            echo "<tr>
                <td>$no</td>
                <td>$nama</td>
                <td>$kelompokData</td>
                <td>$desaData</td>
                <td>$total</td>
                <td style='text-align:center;'>
                    <a href='hapus_hadir.php?uid=$uid' class='delete-btn' onclick=\"return confirm('Yakin ingin menghapus data hadir untuk $nama?')\" title='Hapus Kehadiran'>🗑️</a>
                </td>
            </tr>";
            $no++;
        }
        if ($no === 1) {
            echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>Data tidak ditemukan untuk filter yang dipilih.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>
