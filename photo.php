<?php
require 'config.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Periksa apakah album_id ada di URL
if (!isset($_GET['album_id'])) {
    echo "Album tidak ditemukan.";
    exit;
}

// Ambil album_id dari URL
$album_id = $_GET['album_id'];

// Ambil informasi album berdasarkan album_id
$stmt_album = $pdo->prepare("SELECT * FROM gallery_album WHERE AlbumID = ? AND UserID = ?");
$stmt_album->execute([$album_id, $_SESSION['user_id']]);
$album = $stmt_album->fetch();

// Jika album tidak ditemukan
if (!$album) {
    echo "Album tidak ditemukan atau Anda tidak memiliki akses ke album ini.";
    exit;
}

// Ambil foto-foto untuk album ini
$stmt_photos = $pdo->prepare("SELECT * FROM gallery_foto WHERE AlbumID = ?");
$stmt_photos->execute([$album_id]);
$photos = $stmt_photos->fetchAll();

// Cek jika pengguna ingin menghapus foto
if (isset($_POST['delete'])) {
    $foto_id = $_POST['foto_id'];
    $user_id = $_SESSION['user_id'];

    // Ambil informasi file untuk dihapus dari direktori
    $stmt_get_file = $pdo->prepare("SELECT LokasiFile FROM gallery_foto WHERE FotoID = ? AND AlbumID = ?");
    $stmt_get_file->execute([$foto_id, $album_id]);
    $foto = $stmt_get_file->fetch();

    if ($foto) {
        // Hapus file dari direktori
        $lokasi_file = $foto['LokasiFile'];
        if (file_exists($lokasi_file)) {
            unlink($lokasi_file); // Menghapus file dari server
        }

        // Hapus entri dari database
        $stmt_delete = $pdo->prepare("DELETE FROM gallery_foto WHERE FotoID = ? AND AlbumID = ?");
        $stmt_delete->execute([$foto_id, $album_id]);
    }

    // Redirect agar tidak ada masalah dengan refresh page
    header("Location: photo.php?album_id=$album_id");
    exit;
}

// Ambil jumlah like untuk setiap foto
$like_counts = [];
foreach ($photos as $photo) {
    $stmt_like_count = $pdo->prepare("SELECT COUNT(*) FROM gallery_likesfoto WHERE FotoID = ?");
    $stmt_like_count->execute([$photo['FotoID']]);
    $like_counts[$photo['FotoID']] = $stmt_like_count->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foto di Album: <?php echo htmlspecialchars($album['NamaAlbum']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .photo-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .card-img-top {
            object-fit: cover;
        }
    </style>
</head>
<body>

<?php require 'navbar.php' ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Foto di Album: <?php echo htmlspecialchars($album['NamaAlbum']); ?></h2>
        <a href="add_photo.php?album_id=<?php echo htmlspecialchars($album_id); ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Foto</a>
    </div>

    <?php if (count($photos) > 0): ?>
        <div class="row">
            <?php foreach ($photos as $photo): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 position-relative">
                        <img src="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" class="card-img-top" alt="Foto Album" style="height: 400px;">

                        <!-- Tombol Hapus -->
                        <div class="photo-overlay">
                            <form method="POST" onsubmit="return confirm('Anda yakin ingin menghapus foto ini?');">
                                <input type="hidden" name="foto_id" value="<?php echo htmlspecialchars($photo['FotoID']); ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">Tidak ada foto di album ini.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
