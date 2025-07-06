<?php
session_start();
date_default_timezone_set('Asia/Jakarta'); // <== TAMBAHKAN INI
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit();
}


// Koneksi ke database
$host = "sql107.infinityfree.com";
$user = "if0_39398130";
$pass = "infinityfree354";
$db   = "if0_39398130_presensi_generus";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil UID dari form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $uid = mysqli_real_escape_string($conn, $_POST['uid']);

    // Cek apakah UID terdaftar
    $query = mysqli_query($conn, "SELECT * FROM peserta WHERE uid = '$uid'");
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $id_peserta = $data['id'];

        // Simpan presensi
        $tanggal = date('Y-m-d');
        $waktu   = date('H:i:s');

        // Cek apakah sudah presensi hari ini
        $cek = mysqli_query($conn, "SELECT * FROM data_presensi WHERE id_peserta = $id_peserta AND tanggal = '$tanggal'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($conn, "INSERT INTO data_presensi (id_peserta, tanggal, waktu) VALUES ($id_peserta, '$tanggal', '$waktu')");
            echo "<script>alert('Presensi berhasil!'); window.location='form_presensi.php';</script>";
        } else {
            echo "<script>alert('Generus sudah presensi hari ini.'); window.location='form_presensi.php';</script>";
        }
    } else {
        echo "<script>alert('UID tidak ditemukan!'); window.location='form_presensi.php';</script>";
    }
}
?>
