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

    // Ambil data foto berdasarkan FotoID
    $stmt = $pdo->prepare("SELECT * FROM gallery_foto WHERE FotoID = ?");
    $stmt->execute([$foto_id]);
    $photo = $stmt->fetch();

    if (!$photo) {
        echo "Foto tidak ditemukan.";
        exit();
    }
} else {
    echo "ID foto tidak ada.";
    exit();
}

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $lokasi_file = $_FILES['lokasi_file']['name']; // Mendapatkan nama file baru
    $album_id = $photo['AlbumID']; // Menggunakan AlbumID yang tidak diedit
    $user_id = $photo['UserID']; // Menggunakan UserID yang tidak diedit

    // Jika file baru diunggah, lakukan pemindahan file
    if (!empty($lokasi_file)) {
        // Simpan file ke direktori yang diinginkan
        move_uploaded_file($_FILES['lokasi_file']['tmp_name'], "uploads/" . $lokasi_file);
    } else {
        // Jika tidak ada file baru, tetap menggunakan lokasi file lama
        $lokasi_file = $photo['LokasiFile'];
    }

    // Update data foto di database
    $stmt_update = $pdo->prepare("UPDATE gallery_foto SET JudulFoto = ?, DeskripsiFoto = ?, LokasiFile = ? WHERE FotoID = ?");
    $stmt_update->execute([$judul, $deskripsi, $lokasi_file, $foto_id]);

    header("Location: photos.php?message=Foto berhasil diupdate");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto</title>
    <!-- Link Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Foto</h2>

        <!-- Tampilkan foto saat ini -->
        <div class="mb-4">
            <label>Foto Saat Ini:</label><br>
            <?php if (!empty($photo['LokasiFile'])): ?>
                <img src="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" alt="Foto" class="img-fluid" style="max-height: 300px;">
            <?php else: ?>
                <p>Foto tidak tersedia.</p>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="judul">Judul Foto:</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($photo['JudulFoto']); ?>" required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" required><?php echo htmlspecialchars($photo['DeskripsiFoto']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="lokasi_file">Lokasi File (opsional):</label>
                <input type="file" class="form-control-file" id="lokasi_file" name="lokasi_file">
                <small class="form-text text-muted">Silakan unggah file baru jika diperlukan.</small>
            </div>

            <div class="form-group">
                <label for="album_id">Album ID:</label>
                <input type="number" class="form-control" id="album_id" name="album_id" value="<?php echo htmlspecialchars($photo['AlbumID']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="user_id">User ID:</label>
                <input type="number" class="form-control" id="user_id" name="user_id" value="<?php echo htmlspecialchars($photo['UserID']); ?>" readonly>
            </div>

            <button type="submit" class="btn btn-primary">Update Foto</button>
            <a href="photos.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <!-- Link Bootstrap JS dan jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
