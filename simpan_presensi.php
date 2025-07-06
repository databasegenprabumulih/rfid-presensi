<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set zona waktu ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Koneksi database
$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['uid'])) {
    $uid = trim($_POST['uid']);
    if ($uid !== '') {
        // Sanitasi input
        $uid_safe = mysqli_real_escape_string($conn, $uid);

        // Cek data peserta
        $check = mysqli_query($conn, "SELECT nama_generus, kelompok, desa FROM peserta WHERE uid = '$uid_safe'");
        if (mysqli_num_rows($check) > 0) {
            $data = mysqli_fetch_assoc($check);
            $nama     = htmlspecialchars($data['nama_generus']);
            $kelompok = htmlspecialchars($data['kelompok']);
            $desa     = htmlspecialchars($data['desa']);

            // Ambil waktu saat ini dalam zona waktu Jakarta
            $tanggal_wib = date('Y-m-d');
            $waktu_wib   = date('Y-m-d H:i:s');
            $jam_wib     = date('H:i:s');

            // Cek apakah sudah presensi hari ini
            $cekPresensi = mysqli_query($conn, "SELECT * FROM data_presensi WHERE uid = '$uid_safe' AND DATE(waktu) = '$tanggal_wib'");
            if (mysqli_num_rows($cekPresensi) > 0) {
                echo "<div style='color:#cc0000; font-family:sans-serif; text-align:center; font-weight:bold; font-size:18px;'>";
                echo "❌ Anda sudah melakukan presensi hari ini.";
                echo "</div>";
            } else {
                $query = "INSERT INTO data_presensi (uid, waktu) VALUES ('$uid_safe', '$waktu_wib')";
                if (mysqli_query($conn, $query)) {
                    echo "<div style='color:#11698e; font-family:sans-serif; padding:30px; background:#f0faff; border-radius:15px; max-width:500px; margin:auto; text-align:center; box-shadow:0 0 10px rgba(0,0,0,0.1);'>";
                    echo "<p style='font-size:26px; font-weight:bold;'>$nama</p>";
                    echo "<p style='font-size:20px; font-weight:bold;'>$kelompok</p>";
                    echo "<p style='font-size:20px; font-weight:bold;'>$desa</p>";
                    echo "<p style='font-size:20px; font-weight:bold;'>$jam_wib</p>";
                    echo "<p style='font-size:36px; font-weight:bold; font-family:\"Arabic Typesetting\", serif; color:#007b5e; margin-top:30px;'>الحمد لله جزاكم الله خيرا</p>";
                    echo "</div>";
                } else {
                    echo "<span style='color:red;'>❌ Gagal menyimpan: " . mysqli_error($conn) . "</span>";
                }
            }
        } else {
            echo "<span style='color:red;'>❌ UID tidak ditemukan dalam data generus.</span>";
        }
    } else {
        echo "<span style='color:red;'>❌ UID tidak boleh kosong.</span>";
    }
}
?>
