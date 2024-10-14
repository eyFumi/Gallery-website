<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Cek apakah parameter FotoID ada di URL
if (isset($_GET['FotoID'])) {
    $foto_id = $_GET['FotoID'];

    try {
        // Mulai transaksi
        $pdo->beginTransaction();

        // Hapus komentar terkait foto
        $stmt_comments = $pdo->prepare("DELETE FROM gallery_komentarfoto WHERE FotoID = ?");
        $stmt_comments->execute([$foto_id]);

        // Hapus like terkait foto
        $stmt_likes = $pdo->prepare("DELETE FROM gallery_likesfoto WHERE FotoID = ?");
        $stmt_likes->execute([$foto_id]);

        // Hapus foto dari database
        $stmt_photo = $pdo->prepare("DELETE FROM gallery_foto WHERE FotoID = ?");
        $stmt_photo->execute([$foto_id]);

        // Commit transaksi
        $pdo->commit();

        // Redirect ke halaman photos setelah penghapusan
        header("Location: photos.php?message=Foto beserta komentar dan like berhasil dihapus");
        exit();

    } catch (Exception $e) {
        // Rollback jika terjadi kesalahan
        $pdo->rollBack();
        header("Location: photos.php?error=Terjadi kesalahan saat menghapus data");
        exit();
    }

} else {
    header("Location: photos.php?error=ID foto tidak ditemukan");
    exit();
}
?>
