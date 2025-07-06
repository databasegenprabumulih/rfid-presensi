<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$isAdmin = $_SESSION['role'] === 'admin';
$query = "SELECT * FROM acara ORDER BY tanggal_mulai DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Acara</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f1fdfd;
      padding: 40px;
    }
    h2 {
      text-align: center;
      color: #11698e;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    th {
      background: #11698e;
      color: white;
    }
    tr:hover {
      background-color: #f0f8ff;
    }
    .action-btn {
      background: none;
      border: none;
      cursor: pointer;
      color: #d9534f;
      font-size: 18px;
    }
    .edit-btn {
      color: #0275d8;
    }
    .add-button {
      display: inline-block;
      padding: 10px 20px;
      background: #11698e;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: bold;
      margin-bottom: 20px;
    }
    .add-button:hover {
      background: #0b4f6c;
    }
  </style>
</head>
<body>

<h2>Daftar Acara</h2>

<?php if ($isAdmin): ?>
  <a href="tambah_acara.php" class="add-button"><i class="fa fa-plus"></i> Tambah Acara</a>
<?php endif; ?>

<table>
  <thead>
    <tr>
      <th>Judul</th>
      <th>Deskripsi</th>
      <th>Tanggal Mulai</th>
      <th>Tanggal Selesai</th>
      <?php if ($isAdmin): ?><th>Aksi</th><?php endif; ?>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?= htmlspecialchars($row['judul']) ?></td>
        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
        <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
        <td><?= htmlspecialchars($row['tanggal_selesai']) ?></td>
        <?php if ($isAdmin): ?>
        <td>
          <a href="edit_acara.php?id=<?= $row['id'] ?>" class="action-btn edit-btn"><i class="fa fa-edit"></i></a>
          <a href="hapus_acara.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus acara ini?')" class="action-btn"><i class="fa fa-trash"></i></a>
        </td>
        <?php endif; ?>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

</body>
</html>
