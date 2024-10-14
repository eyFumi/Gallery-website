<?php
require 'config.php';
session_start();

// Inisialisasi variabel untuk pencarian
$search_query = '';
$search_param = '';

// Ambil semua foto dari database
$stmt_photos = $pdo->prepare("SELECT g.*, u.username FROM gallery_foto g JOIN gallery_user u ON g.UserID = u.UserID");
$stmt_photos->execute();
$photos = $stmt_photos->fetchAll();

// Cek apakah ada permintaan pencarian
if (isset($_POST['search'])) {
    $search_query = trim($_POST['search']);
    $search_param = "%$search_query%"; // Untuk mencocokkan pencarian

    // Ambil foto berdasarkan judul yang dicari
    $stmt_photos = $pdo->prepare("SELECT g.*, u.username FROM gallery_foto g JOIN gallery_user u ON g.UserID = u.UserID WHERE g.JudulFoto LIKE ?");
    $stmt_photos->execute([$search_param]);
    $photos = $stmt_photos->fetchAll();
}

// Proses like/dislike
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_level = isset($_SESSION['user_level']) ? $_SESSION['user_level'] : null; // Misalnya, user_level = 'user' atau 'admin'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $foto_id = $_POST['foto_id'];
        $action = $_POST['action'];

        if ($action === 'like') {
            // Cek apakah user sudah menyukai foto ini
            $stmt_like_check = $pdo->prepare("SELECT * FROM gallery_likesfoto WHERE FotoID = ? AND UserID = ?");
            $stmt_like_check->execute([$foto_id, $user_id]);
            if ($stmt_like_check->rowCount() == 0) {
                // Tambahkan like
                $stmt_like = $pdo->prepare("INSERT INTO gallery_likesfoto (FotoID, UserID, TanggalLike) VALUES (?, ?, NOW())");
                $stmt_like->execute([$foto_id, $user_id]);
            }
        } elseif ($action === 'dislike') {
            // Hapus like
            $stmt_dislike = $pdo->prepare("DELETE FROM gallery_likesfoto WHERE FotoID = ? AND UserID = ?");
            $stmt_dislike->execute([$foto_id, $user_id]);
        }
    }

    // Proses komentar
    if (isset($_POST['isi_komentar'])) {
        $foto_id = $_POST['foto_id'];
        $isi_komentar = $_POST['isi_komentar'];

        // Tambahkan komentar
        $stmt_komentar = $pdo->prepare("INSERT INTO gallery_komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES (?, ?, ?, NOW())");
        $stmt_komentar->execute([$foto_id, $user_id, $isi_komentar]);
    }

    // Proses hapus foto
    if (isset($_POST['delete_foto_id'])) {
        $delete_foto_id = $_POST['delete_foto_id'];

        // Hapus komentar yang terkait dengan foto
        $stmt_delete_comments = $pdo->prepare("DELETE FROM gallery_komentarfoto WHERE FotoID = ?");
        $stmt_delete_comments->execute([$delete_foto_id]);

        // Hapus like yang terkait dengan foto
        $stmt_delete_likes = $pdo->prepare("DELETE FROM gallery_likesfoto WHERE FotoID = ?");
        $stmt_delete_likes->execute([$delete_foto_id]);

        // Jika user adalah admin, hapus foto dari semua pengguna; jika pengguna biasa, hanya bisa menghapus foto mereka sendiri
        if ($user_level === 'Admin') {
            // Admin bisa menghapus foto apapun
            $stmt_delete = $pdo->prepare("DELETE FROM gallery_foto WHERE FotoID = ?");
            $stmt_delete->execute([$delete_foto_id]);
        } else {
            // Pengguna biasa hanya bisa menghapus foto mereka sendiri
            $stmt_delete = $pdo->prepare("DELETE FROM gallery_foto WHERE FotoID = ? AND UserID = ?");
            $stmt_delete->execute([$delete_foto_id, $user_id]);
        }

        // Segarkan halaman setelah penghapusan
        header("Location: gallery.php");
        exit();
    }
}

// Ambil jumlah like untuk setiap foto
$likes_count = [];
foreach ($photos as $photo) {
    $stmt_likes_count = $pdo->prepare("SELECT COUNT(*) FROM gallery_likesfoto WHERE FotoID = ?");
    $stmt_likes_count->execute([$photo['FotoID']]);
    $likes_count[$photo['FotoID']] = $stmt_likes_count->fetchColumn();
}

// Ambil komentar untuk setiap foto
$comments = [];
foreach ($photos as $photo) {
    $stmt_comments = $pdo->prepare("SELECT c.*, u.username FROM gallery_komentarfoto c JOIN gallery_user u ON c.UserID = u.UserID WHERE c.FotoID = ?");
    $stmt_comments->execute([$photo['FotoID']]);
    $comments[$photo['FotoID']] = $stmt_comments->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Foto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .banner {
            height: 40vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('uploads/beautiful-natural-green-forest.jpg');
            background-size: cover;
        }
        .card {
            position: relative;
        }
        .download-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none; /* Sembunyikan tombol download secara default */
            background-color: rgba(255, 255, 255, 0.7);
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
        }
        
        .card:hover .download-btn {
            display: block; /* Tampilkan tombol download saat foto di-hover */
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="gallery.php">Galeri Foto</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto d-flex align-items-center">
            <li class="nav-item mr-3">
                <!-- Form pencarian -->
                <form method="post" class="form-inline">
                    <div class="input-group input-group-md">
                        <input type="text" class="form-control rounded-0" name="search" placeholder="Cari berdasarkan judul foto..." value="<?php echo htmlspecialchars($search_query); ?>" autocomplete="off">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-dark rounded-0"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </form>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="album.php">Album</a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_level'] === 'Admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Admin</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<div class="container mt-4">
    <div class="container-fluid banner d-flex align-items-center">
        <div class="container text-center text-white">
            <h1>Galeri</h1>
        </div>
    </div>

    <a href="add_photo.php" class="btn btn-success p-2 mt-3 mb-3"><i class="fas fa-plus"></i> Tambah Foto</a> <!-- Tautan untuk mengunggah foto baru -->

    <?php if (count($photos) > 0): ?>
        <div class="row">
            <?php foreach ($photos as $photo): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div style="position: relative;">
                            <a href="detail_photo.php?foto_id=<?php echo $photo['FotoID']; ?>">
                                <img src="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($photo['JudulFoto']); ?>">
                                <div class="photo-title"><?php echo htmlspecialchars($photo['JudulFoto']); ?></div>
                            </a>
                            <a class="download-btn" href="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><small class="text-muted">Diupload oleh <?php echo htmlspecialchars($photo['username']); ?> pada <?php echo date('d M Y', strtotime($photo['TanggalUnggah'])); ?></small></p>
                            <?php if ($user_level === 'Admin' || $photo['UserID'] === $user_id): ?>
                                <form method="post" class="mt-2">
                                    <input type="hidden" name="delete_foto_id" value="<?php echo $photo['FotoID']; ?>">
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Tidak ada foto yang ditemukan.
        </div>
    <?php endif; ?>
</div>

<style>
    /* CSS untuk menampilkan judul foto saat di-hover */
    .photo-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.6); /* Latar belakang gelap dengan transparansi */
        color: white; /* Warna teks putih */
        text-align: center; /* Teks di tengah */
        padding: 10px; /* Jarak dalam elemen */
        opacity: 0; /* Awalnya tersembunyi */
        transition: opacity 0.3s; /* Animasi saat muncul */
    }

    /* Menampilkan judul saat hover */
    .card:hover .photo-title {
        opacity: 1; /* Tampilkan saat di-hover */
    }
</style>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
