<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Koneksi ke database
$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Proses login
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username='$username' AND password=MD5('$password')";
    $result = mysqli_query($conn, $query);

    if ($data = mysqli_fetch_assoc($result)) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = $data['role']; // 'admin' atau 'user'
        header("Location: index.php");
        exit();
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Presensi Generus</title>
  <style>
    body {
      background: linear-gradient(to bottom right, #11698e, #3fcf8e);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
      margin: 0;
      position: relative;
    }
    .judul-utama {
      font-size: 44px;
      font-weight: bold;
      color: #ffffff;
      margin-bottom: 20px;
      text-transform: uppercase;
      text-align: center;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }
    .login-box {
      background: white;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      width: 300px;
    }
    h2 {
      text-align: center;
      color: #11698e;
      margin-bottom: 25px;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 10px;
      border: 1px solid #ccc;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #11698e;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background: #0b4f6c;
    }
    .error {
      color: red;
      text-align: center;
      margin-top: 10px;
    }
    .calendar-btn {
      position: absolute;
      top: 20px;
      right: 20px;
      background-color: #ffffffaa;
      padding: 10px 14px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: bold;
      color: #11698e;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .calendar-btn:hover {
      background-color: #ffffff;
    }
  </style>
</head>
<body>
  <div class="judul-utama">Database Generus Prabumulih</div>
  <div class="login-box">
    <h2>Login</h2>
    <form method="post">
      <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Masuk</button>
    </form>
    <?php if ($error): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
