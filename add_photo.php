<?php
require 'config.php';
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect ke login jika tidak memiliki akses
    exit;
}

// Ambil semua album yang dimiliki pengguna
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM gallery_album WHERE UserID = ?");
$stmt->execute([$user_id]);
$albums = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $album_id = $_POST['album_id'];
    $judul_foto = $_POST['judul_foto'];
    $deskripsi_foto = $_POST['deskripsi_foto'];
    $tanggal_upload = date('Y-m-d'); // Ambil tanggal sekarang

    // Mengupload foto
    if (isset($_FILES['foto'])) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));

        // Menghasilkan nama file acak
        $random_file_name = uniqid('foto_', true) . '.' . $imageFileType; // Menambahkan prefix 'foto_' dan ekstensi asli

        $target_file = $target_dir . $random_file_name;
        $uploadOk = 1;

        // Cek apakah file gambar
        $check = getimagesize($_FILES["foto"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "File bukan gambar.";
            $uploadOk = 0;
        }

        // Cek apakah file sudah ada
        if (file_exists($target_file)) {
            echo "Maaf, file sudah ada.";
            $uploadOk = 0;
        }

        // Cek ukuran file
        if ($_FILES["foto"]["size"] > 500000000) {
            echo "Maaf, file terlalu besar.";
            $uploadOk = 0;
        }

        // Izinkan format file tertentu
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            echo "Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = 0;
        }

        // Cek jika $uploadOk diatur ke 0 oleh kesalahan
        if ($uploadOk == 0) {
            echo "Maaf, file tidak dapat di-upload.";
        } else {
            // Jika semuanya baik, coba untuk upload file
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
                // Simpan informasi foto ke database
                $stmt_photo = $pdo->prepare("INSERT INTO gallery_foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_photo->execute([$judul_foto, $deskripsi_foto, $tanggal_upload, $target_file, $album_id, $user_id]);
                echo "<div class='alert alert-success' role='alert'>Foto berhasil ditambahkan ke album! Silakan <a href='album.php' class='alert-link'>kembali ke album</a>.</div>";
            } else {
                echo "<div class='alert alert-danger' role='alert'>Maaf, terjadi kesalahan saat meng-upload file.</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php require 'navbar.php' ?>

    <div class="container mt-5">
        <h2>Tambah Foto ke Album</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Pilih Album:</label>
                <select name="album_id" class="form-control" required>
                    <option value="">Pilih Album</option>
                    <?php foreach ($albums as $album): ?>
                        <option value="<?php echo htmlspecialchars($album['AlbumID']); ?>"><?php echo htmlspecialchars($album['NamaAlbum']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group form">
                <label>Judul Foto:</label>
                <input type="text" name="judul_foto" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Deskripsi Foto:</label>
                <textarea name="deskripsi_foto" class="form-control"></textarea>
            </div>

            <div class="form-group">
                <label>Upload Foto:</label>
                <input type="file" name="foto" class="form-control-file" required>
            </div>

            <input type="submit" value="Tambah Foto" class="btn btn-primary">
            <a href="album.php" class="btn btn-secondary ml-2">Kembali</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
