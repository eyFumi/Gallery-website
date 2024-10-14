<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Cek apakah KomentarID ada di URL
if (isset($_GET['KomentarID'])) {
    $komentar_id = $_GET['KomentarID'];

    // Hapus komentar dari database
    $stmt_delete = $pdo->prepare("DELETE FROM gallery_komentarfoto WHERE KomentarID = ?");
    $stmt_delete->execute([$komentar_id]);

    // Redirect setelah penghapusan berhasil
    header("Location: comments.php?message=Komentar berhasil dihapus");
    exit();
} else {
    header("Location: comments.php?error=ID komentar tidak ditemukan");
    exit();
}
?>
