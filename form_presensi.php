<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Presensi</title>
    <link rel="stylesheet" href="style.css">

    <!-- Tambahan manifest & icon PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#11698e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f8ff;
            padding: 40px;
        }
        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            background: #11698e;
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .home-link:hover {
            background: #0e5a7a;
        }
        .form-container {
            max-width: 400px;
            margin: 80px auto 0 auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #11698e;
            margin-bottom: 20px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #a7d6ff;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #20c997;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 12px;
            cursor: pointer;
        }
        button:hover {
            background-color: #17a589;
        }
        #pesan {
            margin-top: 15px;
            text-align: center;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-link">← Beranda</a>

    <div class="form-container">
        <h2>Form Presensi</h2>
        <form id="presensiForm">
            <input type="text" name="uid" id="uid" placeholder="Masukkan UID" required>
            <button type="submit">Presensi</button>
        </form>
        <div id="pesan"></div>
    </div>

    <script>
    document.getElementById("presensiForm").addEventListener("submit", function(e) {
        e.preventDefault();
        var uid = document.getElementById("uid").value;
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "simpan_presensi.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            document.getElementById("pesan").innerHTML = this.responseText;
            document.getElementById("uid").value = "";
        };
        xhr.send("uid=" + encodeURIComponent(uid));
    });

    // Tambahkan pendaftaran service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker.js')
                .then(reg => console.log('Service Worker terdaftar:', reg.scope))
                .catch(err => console.error('Service Worker gagal:', err));
        });
    }
</script>
</body>
</html>
