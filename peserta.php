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
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Handle CSV Upload
if (isset($_POST['upload_csv'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if ($_FILES['csv_file']['error'] === 0 && is_uploaded_file($file)) {
        $handle = fopen($file, 'r');
        fgetcsv($handle); // skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $uid = mysqli_real_escape_string($conn, $data[0]);
            $nama = mysqli_real_escape_string($conn, $data[1]);
            $kelompok = mysqli_real_escape_string($conn, $data[2]);
            $desa = mysqli_real_escape_string($conn, $data[3]);
            mysqli_query($conn, "INSERT INTO peserta (uid, nama_generus, kelompok, desa) VALUES ('$uid', '$nama', '$kelompok', '$desa')");
        }
        fclose($handle);
        header("Location: peserta.php");
        exit();
    }
}

// Handle Export Excel
if (isset($_GET['export'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data_generus.xls");
    echo "<table border='1'><tr><th>UID</th><th>Nama Generus</th><th>Kelompok</th><th>Desa</th></tr>";
    $export_q = "SELECT uid, nama_generus, kelompok, desa FROM peserta";
    $filters = [];
    if (!empty($_GET['desa'])) {
        $desa = mysqli_real_escape_string($conn, $_GET['desa']);
        $filters[] = "desa = '$desa'";
    }
    if (!empty($_GET['kelompok'])) {
        $kelompok = mysqli_real_escape_string($conn, $_GET['kelompok']);
        $filters[] = "kelompok = '$kelompok'";
    }
    if ($filters) {
        $export_q .= " WHERE " . implode(" AND ", $filters);
    }
    $export_r = mysqli_query($conn, $export_q);
    while ($r = mysqli_fetch_assoc($export_r)) {
        echo "<tr><td>{$r['uid']}</td><td>{$r['nama_generus']}</td><td>{$r['kelompok']}</td><td>{$r['desa']}</td></tr>";
    }
    echo "</table>";
    exit();
}

// Hapus peserta satuan
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($conn, "DELETE FROM peserta WHERE id = $id");
    header("Location: peserta.php");
    exit();
}

// Hapus semua peserta
if (isset($_POST['hapus_semua'])) {
    mysqli_query($conn, "DELETE FROM peserta");
    header("Location: peserta.php");
    exit();
}

// Hapus peserta berdasarkan desa
if (isset($_POST['hapus_desa'])) {
    $desa_terpilih = mysqli_real_escape_string($conn, $_POST['desa_terpilih']);
    mysqli_query($conn, "DELETE FROM peserta WHERE desa = '$desa_terpilih'");
    header("Location: peserta.php?desa=" . urlencode($desa_terpilih));
    exit();
}

// Hapus peserta berdasarkan kelompok
if (isset($_POST['hapus_kelompok'])) {
    $kelompok_terpilih = mysqli_real_escape_string($conn, $_POST['kelompok_terpilih']);
    mysqli_query($conn, "DELETE FROM peserta WHERE kelompok = '$kelompok_terpilih'");
    header("Location: peserta.php?kelompok=" . urlencode($kelompok_terpilih));
    exit();
}

$filter_desa = isset($_GET['desa']) ? mysqli_real_escape_string($conn, $_GET['desa']) : '';
$filter_kelompok = isset($_GET['kelompok']) ? mysqli_real_escape_string($conn, $_GET['kelompok']) : '';

$desa_list = [];
$kelompok_list = [];

$res_desa = mysqli_query($conn, "SELECT DISTINCT desa FROM peserta ORDER BY desa ASC");
while ($row = mysqli_fetch_assoc($res_desa)) {
    $desa_list[] = $row['desa'];
}

$res_kelompok = mysqli_query($conn, "SELECT DISTINCT kelompok FROM peserta ORDER BY kelompok ASC");
while ($row = mysqli_fetch_assoc($res_kelompok)) {
    $kelompok_list[] = $row['kelompok'];
}

$where = [];
if ($filter_desa !== '') $where[] = "desa = '$filter_desa'";
if ($filter_kelompok !== '') $where[] = "kelompok = '$filter_kelompok'";
$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT * FROM peserta $where_sql ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Data Generus</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f9f9f9; }
        .container { max-width: 1100px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        h2 { margin-bottom: 20px; color: #11698e; }
        .filter-form { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .filter-form label { font-weight: bold; }
        select { padding: 6px 10px; border-radius: 5px; border: 1px solid #ccc; }
        .btn { background-color: #11698e; color: white; padding: 8px 14px; text-decoration: none; border-radius: 7px; margin-right: 10px; display: inline-block; cursor: pointer; border: none; font-size: 14px; }
        .btn:hover { background-color: #0d536b; }
        table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #11698e; color: white; }
        td.action a { margin-right: 10px; font-size: 16px; text-decoration: none; background: none; padding: 6px; border-radius: 6px; display: inline-block; }
        td.action a.edit i { color: #11698e; }
        td.action a.delete i { color: #c0392b; }
        td.action a:hover { opacity: 0.8; background: #f0f0f0; }
    </style>
</head>
<body>
<div class="container">
    <h2>Data Generus</h2>

    <form method="GET" class="filter-form">
        <label for="desa">Desa:</label>
        <select name="desa" id="desa" onchange="updateKelompokDropdown();">
            <option value="">-- Semua Desa --</option>
            <?php foreach ($desa_list as $desa): ?>
                <option value="<?= htmlspecialchars($desa) ?>" <?= $desa === $filter_desa ? 'selected' : '' ?>>
                    <?= htmlspecialchars($desa) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="kelompok">Kelompok:</label>
        <select name="kelompok" id="kelompok" onchange="this.form.submit()">
            <option value="">-- Semua Kelompok --</option>
            <?php foreach ($kelompok_list as $kelompok): ?>
                <?php
                $is_valid = ($filter_desa === '') ||
                    mysqli_fetch_assoc(mysqli_query($conn, "SELECT 1 FROM peserta WHERE desa = '$filter_desa' AND kelompok = '$kelompok' LIMIT 1"));
                if ($is_valid):
                ?>
                <option value="<?= htmlspecialchars($kelompok) ?>" <?= $kelompok === $filter_kelompok ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kelompok) ?>
                </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn">Filter</button></noscript>
    </form>

    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="tambah_peserta.php" class="btn">+ Tambah Generus</a>

        <form method="post" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
            <label for="csv_file" style="font-weight: normal;">Upload CSV <small>(UID,Nama,Kelompok,Desa)</small>:</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            <button type="submit" name="upload_csv" class="btn">📥 Upload</button>
        </form>

        <a href="?export=1<?= $filter_desa ? '&desa=' . urlencode($filter_desa) : '' ?><?= $filter_kelompok ? '&kelompok=' . urlencode($filter_kelompok) : '' ?>" class="btn" style="background: #0b4f6c;">📤 Export ke Excel</a>
    </div>

    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>UID</th>
            <th>Nama Generus</th>
            <th>Kelompok</th>
            <th>Desa</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['uid']) ?></td>
                    <td><?= htmlspecialchars($row['nama_generus']) ?></td>
                    <td><?= htmlspecialchars($row['kelompok']) ?></td>
                    <td><?= htmlspecialchars($row['desa']) ?></td>
                    <td class="action">
                        <a href="edit_peserta.php?id=<?= $row['id'] ?>" class="edit" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        <a href="peserta.php?hapus=<?= $row['id'] ?>" class="delete" onclick="return confirm('Yakin ingin menghapus generus ini?')" title="Hapus">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center; color:#999;">Data peserta tidak ditemukan.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Tombol hapus di bawah tabel -->
    <div style="display: flex; gap: 10px; align-items: center; margin-top: 20px; flex-wrap: wrap;">
        <?php if (empty($filter_desa) && empty($filter_kelompok)): ?>
            <form method="post" onsubmit="return confirm('Yakin ingin menghapus SEMUA peserta?')">
                <button type="submit" name="hapus_semua" class="btn" style="background:#c0392b;">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($filter_desa)): ?>
            <form method="post" onsubmit="return confirm('Yakin ingin menghapus semua peserta dari desa ini?')">
                <input type="hidden" name="desa_terpilih" value="<?= htmlspecialchars($filter_desa) ?>">
                <button type="submit" name="hapus_desa" class="btn" style="background:#e74c3c;">
                    <i class="fas fa-trash"></i> Hapus Semua Desa: <?= htmlspecialchars($filter_desa) ?>
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($filter_kelompok)): ?>
            <form method="post" onsubmit="return confirm('Yakin ingin menghapus semua peserta dari kelompok ini?')">
                <input type="hidden" name="kelompok_terpilih" value="<?= htmlspecialchars($filter_kelompok) ?>">
                <button type="submit" name="hapus_kelompok" class="btn" style="background:#d35400;">
                    <i class="fas fa-trash"></i> Hapus Semua Kelompok: <?= htmlspecialchars($filter_kelompok) ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function updateKelompokDropdown() {
    const desa = document.getElementById('desa').value;
    const url = new URL(window.location.href);
    url.searchParams.set('desa', desa);
    url.searchParams.delete('kelompok');
    window.location.href = url.toString();
}
</script>
</body>
</html>
