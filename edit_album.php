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

    // Ambil data album berdasarkan AlbumID
    $stmt_album = $pdo->prepare("SELECT * FROM gallery_album WHERE AlbumID = ?");
    $stmt_album->execute([$album_id]);
    $album = $stmt_album->fetch();

    if (!$album) {
        header("Location: albums.php?error=Album tidak ditemukan");
        exit();
    }

    // Jika form disubmit, update album
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_album = $_POST['nama_album'];
        $deskripsi_album = $_POST['deskripsi_album'];

        // Update data album
        $stmt_update = $pdo->prepare("UPDATE gallery_album SET NamaAlbum = ?, Deskripsi = ? WHERE AlbumID = ?");
        $stmt_update->execute([$nama_album, $deskripsi_album, $album_id]);

        // Redirect ke halaman album setelah update
        header("Location: albums.php?message=Album berhasil diperbarui");
        exit();
    }
} else {
    header("Location: albums.php?error=ID album tidak ditemukan");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album</title>
    <!-- Link Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Album</h2>

        <form method="post">
            <div class="form-group">
                <label for="nama_album">Nama Album:</label>
                <input type="text" class="form-control" id="nama_album" name="nama_album" value="<?php echo htmlspecialchars($album['NamaAlbum']); ?>" required>
            </div>

            <div class="form-group">
                <label for="deskripsi_album">Deskripsi Album:</label>
                <textarea class="form-control" id="deskripsi_album" name="deskripsi_album" required><?php echo htmlspecialchars($album['Deskripsi']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="albums.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <!-- Link Bootstrap JS dan jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
