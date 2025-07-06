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

$messageAdmin = "";
$messageUser = "";

if (isset($_POST['ubah_admin'])) {
    $username = mysqli_real_escape_string($conn, $_POST['admin_username']);
    $password_lama = $_POST['admin_password_lama'];
    $password_baru = $_POST['admin_password_baru'];

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data && password_verify($password_lama, $data['password'])) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE admin SET password = '$password_hash' WHERE username = '$username'");
        $messageAdmin = "Password admin berhasil diubah.";
    } else {
        $messageAdmin = "Password lama salah atau akun tidak ditemukan.";
    }
}

if (isset($_POST['ubah_user'])) {
    $user_id = intval($_POST['user_id']);
    $password_baru_user = $_POST['user_password_baru'];

    if ($password_baru_user === '') {
        $messageUser = "Password baru user tidak boleh kosong.";
    } else {
        $password_hash_user = password_hash($password_baru_user, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password = '$password_hash_user' WHERE id = $user_id");
        if ($update) {
            $messageUser = "Password user berhasil diubah.";
        } else {
            $messageUser = "Gagal mengubah password user: " . mysqli_error($conn);
        }
    }
}

$usersResult = mysqli_query($conn, "SELECT id, username FROM users ORDER BY username ASC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Pengaturan Akun</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f8ff;
            padding: 40px;
        }
        .toggle-header {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #11698e;
            font-weight: bold;
            font-size: 1.3rem;
            cursor: pointer;
            user-select: none;
            margin-bottom: 12px;
            width: 140px;
            border: 2px solid #11698e;
            border-radius: 10px;
            padding: 10px 0;
            transition: background-color 0.3s ease;
        }
        .toggle-header:hover {
            background-color: #d0e8f2;
        }
        .key-icon {
            font-size: 32px;
            user-select: none;
            color: #11698e;
        }
        form {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            max-width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
            display: none;
        }
        label {
            display: block;
            margin-bottom: 6px;
            margin-top: 12px;
            color: #333;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            background-color: #11698e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        .message {
            margin-top: 15px;
            font-weight: bold;
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>

   <div class="toggle-header" id="toggleAdmin" title="Klik untuk ubah password admin">
    <div class="key-icon">🔑</div>
    <div>Admin</div>
</div>

    <form method="POST" id="formAdmin">
        <label>Username Admin</label>
        <input type="text" name="admin_username" required placeholder="Username admin">

        <label>Password Lama</label>
        <input type="password" name="admin_password_lama" required placeholder="Password lama">

        <label>Password Baru</label>
        <input type="password" name="admin_password_baru" required placeholder="Password baru">

        <button type="submit" name="ubah_admin">Ubah Password Admin</button>

        <?php if ($messageAdmin): ?>
            <div class="message"><?= htmlspecialchars($messageAdmin) ?></div>
        <?php endif; ?>
    </form>

    <div class="toggle-header" id="toggleUser" title="Klik untuk ubah password user">
    <div class="key-icon">🔑</div>
    <div>User</div>
</div>

    <form method="POST" id="formUser">
        <label>Pilih User</label>
        <select name="user_id" required>
            <option value="">-- Pilih User --</option>
            <?php while ($user = mysqli_fetch_assoc($usersResult)): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Password Baru</label>
        <input type="password" name="user_password_baru" required placeholder="Password baru untuk user">

        <button type="submit" name="ubah_user">Ubah Password User</button>

        <?php if ($messageUser): ?>
            <div class="message"><?= htmlspecialchars($messageUser) ?></div>
        <?php endif; ?>
    </form>

<script>
    const toggleAdmin = document.getElementById('toggleAdmin');
    const formAdmin = document.getElementById('formAdmin');
    const toggleUser = document.getElementById('toggleUser');
    const formUser = document.getElementById('formUser');

    toggleAdmin.addEventListener('click', () => {
        if (formAdmin.style.display === 'block') {
            formAdmin.style.display = 'none';
        } else {
            formAdmin.style.display = 'block';
            formUser.style.display = 'none';
        }
    });

    toggleUser.addEventListener('click', () => {
        if (formUser.style.display === 'block') {
            formUser.style.display = 'none';
        } else {
            formUser.style.display = 'block';
            formAdmin.style.display = 'none';
        }
    });
</script>

</body>
</html>
