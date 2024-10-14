<?php
require 'config.php'; // Pastikan config.php terhubung dengan database gallery_album
session_start();

// // Periksa apakah pengguna sudah login dan memiliki level User atau Admin
// if (!isset($_SESSION['user_id']) || ($_SESSION['level'] !== 'User' && $_SESSION['level'] !== 'Admin')) {
//     header("Location: login.php"); // Redirect ke login jika tidak memiliki akses
//     exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_album = $_POST['nama_album'];
    $deskripsi = $_POST['deskripsi'];
    $user_id = $_SESSION['user_id']; // Ambil UserID dari sesi

    // Menyimpan album baru ke database gallery_album
    $stmt = $pdo->prepare("INSERT INTO gallery_album (NamaAlbum, Deskripsi, TanggalDibuat, UserID) VALUES (?, ?, CURDATE(), ?)");
    if ($stmt->execute([$nama_album, $deskripsi, $user_id])) {
        echo "<div class='alert alert-success'>Album berhasil dibuat! Silakan <a href='album.php'>kembali ke Album</a>.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal membuat album!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Album</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php require 'navbar.php' ?>

<div class="container mt-4">
    <h2>Buat Album Baru</h2>
    <form method="POST" class="form-group">
        <div class="form-group">
            <label for="nama_album">Nama Album:</label>
            <input type="text" class="form-control" name="nama_album" id="nama_album" required>
        </div>

        <div class="form-group">
            <label for="deskripsi">Deskripsi:</label>
            <textarea class="form-control" name="deskripsi" id="deskripsi" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Buat Album</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
