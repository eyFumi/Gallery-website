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

    // Ambil data komentar berdasarkan KomentarID
    $stmt_comment = $pdo->prepare("SELECT * FROM gallery_komentarfoto WHERE KomentarID = ?");
    $stmt_comment->execute([$komentar_id]);
    $comment = $stmt_comment->fetch();

    if (!$comment) {
        header("Location: comments.php?error=Komentar tidak ditemukan");
        exit();
    }

    // Jika form disubmit, update komentar
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isi_komentar = $_POST['isi_komentar'];

        // Update isi komentar
        $stmt_update = $pdo->prepare("UPDATE gallery_komentarfoto SET IsiKomentar = ? WHERE KomentarID = ?");
        $stmt_update->execute([$isi_komentar, $komentar_id]);

        // Redirect ke halaman komentar setelah update
        header("Location: comments.php?message=Komentar berhasil diperbarui");
        exit();
    }
} else {
    header("Location: comments.php?error=ID komentar tidak ditemukan");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Komentar</title>
</head>
<body>
    <h2>Edit Komentar</h2>

    <form method="post">
        <label for="isi_komentar">Isi Komentar:</label>
        <textarea id="isi_komentar" name="isi_komentar" required><?php echo htmlspecialchars($comment['IsiKomentar']); ?></textarea><br>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>
