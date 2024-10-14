<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Cek apakah AlbumID ada di URL
if (isset($_GET['AlbumID'])) {
    $album_id = $_GET['AlbumID'];

    try {
        // Mulai transaksi
        $pdo->beginTransaction();

        // Hapus semua foto terkait dengan album
        $stmt_photos = $pdo->prepare("DELETE FROM gallery_foto WHERE AlbumID = ?");
        $stmt_photos->execute([$album_id]);

        // Hapus album dari database
        $stmt_album = $pdo->prepare("DELETE FROM gallery_album WHERE AlbumID = ?");
        $stmt_album->execute([$album_id]);

        // Commit transaksi
        $pdo->commit();

        // Redirect setelah penghapusan berhasil
        header("Location: albums.php?message=Album dan foto berhasil dihapus");
        exit();

    } catch (Exception $e) {
        // Rollback jika terjadi kesalahan
        $pdo->rollBack();
        header("Location: albums.php?error=Terjadi kesalahan saat menghapus album");
        exit();
    }

} else {
    header("Location: albums.php?error=ID album tidak ditemukan");
    exit();
}
?>
