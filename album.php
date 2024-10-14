<?php
require 'config.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect ke login jika tidak memiliki akses
    exit;
}

// Ambil semua album dari pengguna
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM gallery_album WHERE UserID = ?");
$stmt->execute([$user_id]);
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC); // Pastikan hasilnya dalam format array asosiatif
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Album Saya</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles for album page */
        .album {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .album h3 {
            margin: 0;
            color: #444;
        }

        .album p {
            margin: 5px 0;
        }

        .photos {
            display: flex;
            flex-wrap: wrap;
            gap: 10px; /* Spasi antara foto */
        }

        .photos img {
            border-radius: 5px;
            transition: transform 0.2s;
            width: 100px; /* Ukuran foto */
            height: auto;
        }

        .photos img:hover {
            transform: scale(1.1); /* Efek zoom saat hover */
        }

        /* Link buat album lebih menonjol */
        .create-album {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .create-album:hover {
            background-color: #0056b3;
        }

        /* Responsif */
        @media (max-width: 600px) {
            .photos img {
                width: 80px; /* Ukuran foto yang lebih kecil di perangkat kecil */
            }
        }
    </style>
</head>
<body>
<?php require 'navbar.php' ?>

<div class="container mt-4">
    <h2>Album Saya</h2>
    <a href="create_album.php" class="btn btn-primary create-album"><i class="fas fa-plus-circle"></i> Buat Album</a>
    <?php if (count($albums) > 0): ?>
        <?php foreach ($albums as $album): ?>
            <div class="album">
                <h3>
                    <a href="photo.php?album_id=<?php echo htmlspecialchars($album['AlbumID']); ?>">
                        <i class="fas fa-folder-open"></i> <?php echo htmlspecialchars($album['NamaAlbum']); ?>
                    </a>
                </h3>
                <p><?php echo htmlspecialchars($album['Deskripsi']); ?></p>
                <p><i class="fas fa-calendar-alt"></i> Tanggal Dibuat: <?php echo htmlspecialchars($album['TanggalDibuat']); ?></p>

                <?php
                // Ambil foto-foto untuk album ini
                $stmt_photos = $pdo->prepare("SELECT * FROM gallery_foto WHERE AlbumID = ?");
                $stmt_photos->execute([$album['AlbumID']]);
                $photos = $stmt_photos->fetchAll(PDO::FETCH_ASSOC); // Mengambil hasil sebagai array asosiatif
                ?>

                <?php if (count($photos) > 0): ?>
                    <div class="photos">
                        <?php foreach ($photos as $photo): ?>
                            <img src="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" alt="Foto Album" class="img-thumbnail">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><i class="fas fa-exclamation-circle"></i> Tidak ada foto di album ini.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p><i class="fas fa-info-circle"></i> Anda belum membuat album.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
