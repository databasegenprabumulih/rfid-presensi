<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah admin
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
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses tambah desa
if (isset($_POST['tambah_desa'])) {
    $nama_desa = mysqli_real_escape_string($conn, $_POST['nama_desa']);
    if ($nama_desa != '') {
        mysqli_query($conn, "INSERT INTO desa (nama_desa) VALUES ('$nama_desa')");
        header("Location: kelola_wilayah.php");
        exit();
    }
}

// Proses tambah kelompok
if (isset($_POST['tambah_kelompok'])) {
    $nama_kelompok = mysqli_real_escape_string($conn, $_POST['nama_kelompok']);
    $desa_id = intval($_POST['desa_id']);
    if ($nama_kelompok != '' && $desa_id > 0) {
        mysqli_query($conn, "INSERT INTO kelompok (nama_kelompok, desa_id) VALUES ('$nama_kelompok', $desa_id)");
        header("Location: kelola_wilayah.php");
        exit();
    }
}

// Proses hapus desa
if (isset($_GET['hapus_desa'])) {
    $id = intval($_GET['hapus_desa']);
    // Hapus dulu kelompok terkait desa ini
    mysqli_query($conn, "DELETE FROM kelompok WHERE desa_id = $id");
    // Baru hapus desa
    mysqli_query($conn, "DELETE FROM desa WHERE id = $id");
    header("Location: kelola_wilayah.php");
    exit();
}

// Proses hapus kelompok
if (isset($_GET['hapus_kelompok'])) {
    $id = intval($_GET['hapus_kelompok']);
    mysqli_query($conn, "DELETE FROM kelompok WHERE id = $id");
    header("Location: kelola_wilayah.php");
    exit();
}

// Proses edit desa
if (isset($_POST['edit_desa'])) {
    $id = intval($_POST['desa_id_edit']);
    $nama_desa = mysqli_real_escape_string($conn, $_POST['nama_desa_edit']);
    if ($id > 0 && $nama_desa != '') {
        mysqli_query($conn, "UPDATE desa SET nama_desa = '$nama_desa' WHERE id = $id");
        header("Location: kelola_wilayah.php");
        exit();
    }
}

// Proses edit kelompok
if (isset($_POST['edit_kelompok'])) {
    $id = intval($_POST['kelompok_id_edit']);
    $nama_kelompok = mysqli_real_escape_string($conn, $_POST['nama_kelompok_edit']);
    $desa_id = intval($_POST['desa_id_kelompok_edit']);
    if ($id > 0 && $nama_kelompok != '' && $desa_id > 0) {
        mysqli_query($conn, "UPDATE kelompok SET nama_kelompok = '$nama_kelompok', desa_id = $desa_id WHERE id = $id");
        header("Location: kelola_wilayah.php");
        exit();
    }
}

// Ambil data desa dan kelompok dari database
$desa_list = [];
$res = mysqli_query($conn, "SELECT * FROM desa ORDER BY nama_desa ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $desa_list[] = $row;
}

$kelompok_list = [];
$res = mysqli_query($conn, "SELECT k.*, d.nama_desa FROM kelompok k LEFT JOIN desa d ON k.desa_id = d.id ORDER BY d.nama_desa ASC, k.nama_kelompok ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $kelompok_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Kelola Wilayah (Desa & Kelompok)</title>
<style>
  body {
    font-family: Arial, sans-serif;
    padding: 20px 40px;
    background: #f9f9f9;
  }
  h2 {
    color: #11698e;
  }
  form {
    background: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
  }
  input[type=text], select {
    width: 100%;
    padding: 8px 10px;
    margin: 8px 0 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }
  input[type=submit] {
    background: #11698e;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
  }
  input[type=submit]:hover {
    background: #0a5470;
  }

  /* Form container horizontal */
  .form-container {
    display: flex;
    gap: 30px;
    max-width: 900px;
    margin-bottom: 30px;
  }
  .form-container form {
    flex: 1;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }
  th, td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
  }
  th {
    background: #11698e;
    color: white;
  }
  tr:hover {
    background: #f1f9fb;
  }
  a.action-btn {
    color: #11698e;
    text-decoration: none;
    margin-right: 12px;
    font-weight: bold;
  }
  a.action-btn:hover {
    text-decoration: underline;
  }

  /* Modal overlay */
  .modal {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 120px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
  }

  /* Modal content */
  .modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px 30px;
    border: 1px solid #888;
    width: 400px;
    border-radius: 10px;
    position: relative;
  }

  .close {
    color: #aaa;
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
  }
  .close:hover,
  .close:focus {
    color: black;
  }
</style>
</head>
<body>

<h2>Kelola Wilayah</h2>

<div class="form-container">
  <!-- Form tambah Desa -->
  <form method="POST" action="">
    <h3>Tambah Desa Baru</h3>
    <input type="text" name="nama_desa" placeholder="Nama Desa Baru" required />
    <input type="submit" name="tambah_desa" value="Tambah Desa" />
  </form>

  <!-- Form tambah Kelompok -->
  <form method="POST" action="">
    <h3>Tambah Kelompok Baru</h3>
    <input type="text" name="nama_kelompok" placeholder="Nama Kelompok Baru" required />
    <select name="desa_id" required>
      <option value="">Pilih Desa</option>
      <?php foreach ($desa_list as $desa): ?>
        <option value="<?= $desa['id'] ?>"><?= htmlspecialchars($desa['nama_desa']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="submit" name="tambah_kelompok" value="Tambah Kelompok" />
  </form>
</div>

<h3>Daftar Desa</h3>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Desa</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($desa_list as $index => $desa): ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($desa['nama_desa']) ?></td>
        <td>
          <a href="#" class="action-btn" onclick="openEditDesaModal(<?= $desa['id'] ?>, '<?= htmlspecialchars(addslashes($desa['nama_desa'])) ?>')">Edit</a>
          <a href="?hapus_desa=<?= $desa['id'] ?>" class="action-btn" onclick="return confirm('Yakin hapus desa ini? Semua kelompok di dalamnya juga akan terhapus.')">Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>Daftar Kelompok</h3>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Nama Kelompok</th>
      <th>Desa</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($kelompok_list as $index => $kelompok): ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($kelompok['nama_kelompok']) ?></td>
        <td><?= htmlspecialchars($kelompok['nama_desa']) ?></td>
        <td>
          <a href="#" class="action-btn" onclick="openEditKelompokModal(<?= $kelompok['id'] ?>, '<?= htmlspecialchars(addslashes($kelompok['nama_kelompok'])) ?>', <?= $kelompok['desa_id'] ?>)">Edit</a>
          <a href="?hapus_kelompok=<?= $kelompok['id'] ?>" class="action-btn" onclick="return confirm('Yakin hapus kelompok ini?')">Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Modal Edit Desa -->
<div id="editDesaModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeEditDesaModal()">&times;</span>
    <h3>Edit Desa</h3>
    <form method="POST" action="">
      <input type="hidden" name="desa_id_edit" id="desa_id_edit" />
      <input type="text" name="nama_desa_edit" id="nama_desa_edit" placeholder="Nama Desa" required />
      <input type="submit" name="edit_desa" value="Simpan Perubahan" />
    </form>
  </div>
</div>

<!-- Modal Edit Kelompok -->
<div id="editKelompokModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeEditKelompokModal()">&times;</span>
    <h3>Edit Kelompok</h3>
    <form method="POST" action="">
      <input type="hidden" name="kelompok_id_edit" id="kelompok_id_edit" />
      <input type="text" name="nama_kelompok_edit" id="nama_kelompok_edit" placeholder="Nama Kelompok" required />
      <select name="desa_id_kelompok_edit" id="desa_id_kelompok_edit" required>
        <option value="">Pilih Desa</option>
        <?php foreach ($desa_list as $desa): ?>
          <option value="<?= $desa['id'] ?>"><?= htmlspecialchars($desa['nama_desa']) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="submit" name="edit_kelompok" value="Simpan Perubahan" />
    </form>
  </div>
</div>

<script>
  function openEditDesaModal(id, nama) {
    document.getElementById('desa_id_edit').value = id;
    document.getElementById('nama_desa_edit').value = nama;
    document.getElementById('editDesaModal').style.display = 'block';
  }
  function closeEditDesaModal() {
    document.getElementById('editDesaModal').style.display = 'none';
  }

  function openEditKelompokModal(id, nama, desaId) {
    document.getElementById('kelompok_id_edit').value = id;
    document.getElementById('nama_kelompok_edit').value = nama;
    document.getElementById('desa_id_kelompok_edit').value = desaId;
    document.getElementById('editKelompokModal').style.display = 'block';
  }
  function closeEditKelompokModal() {
    document.getElementById('editKelompokModal').style.display = 'none';
  }

  // Tutup modal jika klik di luar konten modal
  window.onclick = function(event) {
    const desaModal = document.getElementById('editDesaModal');
    const kelompokModal = document.getElementById('editKelompokModal');
    if (event.target == desaModal) {
      desaModal.style.display = 'none';
    }
    if (event.target == kelompokModal) {
      kelompokModal.style.display = 'none';
    }
  }
</script>

</body>
</html>
