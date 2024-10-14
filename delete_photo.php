<?php
require 'config.php';
session_start();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil informasi pengguna dari session
$user_id = $_SESSION['user_id'];
$user_level = $_SESSION['user_level']; // Cek apakah user admin atau biasa

// Proses penghapusan foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_foto_id'])) {
    $foto_id = $_POST['delete_foto_id'];

    // Cek apakah foto tersebut ada di database
    $stmt_foto = $pdo->prepare("SELECT * FROM gallery_foto WHERE FotoID = ?");
    $stmt_foto->execute([$foto_id]);
    $foto = $stmt_foto->fetch();

    if ($foto) {
        // Hanya admin atau pemilik foto yang bisa menghapus
        if ($user_level === 'Admin' || $foto['UserID'] == $user_id) {
            // Hapus foto dari database
            $stmt_delete = $pdo->prepare("DELETE FROM gallery_foto WHERE FotoID = ?");
            $stmt_delete->execute([$foto_id]);

            // Tambahkan logika untuk menghapus file fisik foto jika diperlukan
            if (file_exists($foto['LokasiFile'])) {
                unlink($foto['LokasiFile']); // Hapus file dari server
            }

            header("Location: gallery.php?message=Foto berhasil dihapus.");
            exit();
        } else {
            header("Location: gallery.php?error=Anda tidak memiliki izin untuk menghapus foto ini.");
            exit();
        }
    } else {
        header("Location: gallery.php?error=Foto tidak ditemukan.");
        exit();
    }
} else {
    header("Location: gallery.php");
    exit();
}
