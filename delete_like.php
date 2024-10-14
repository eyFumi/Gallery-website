<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Cek apakah LikeID ada di URL
if (isset($_GET['LikeID'])) {
    $like_id = $_GET['LikeID'];

    // Hapus like dari database
    $stmt_delete = $pdo->prepare("DELETE FROM gallery_likesfoto WHERE LikeID = ?");
    $stmt_delete->execute([$like_id]);

    // Redirect setelah penghapusan berhasil
    header("Location: likes.php?message=Like berhasil dihapus");
    exit();
} else {
    header("Location: likes.php?error=ID like tidak ditemukan");
    exit();
}
?>
