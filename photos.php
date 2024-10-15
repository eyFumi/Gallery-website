<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') { 
    header("Location: login.php");
    exit();
}

// Proses untuk menambah foto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_photo') {
    $judulFoto = $_POST['JudulFoto'];
    $deskripsiFoto = $_POST['DeskripsiFoto'];
    $albumID = $_POST['AlbumID'];
    $userId = $_SESSION['user_id'];

    // Proses upload file
$targetDir = "uploads/"; // Folder untuk menyimpan gambar

// Mengambil ekstensi file
$imageFileType = strtolower(pathinfo($_FILES["LokasiFile"]["name"], PATHINFO_EXTENSION));

// Membuat nama file acak
$randomFileName = uniqid('photo_', true) . '.' . $imageFileType; // Membuat nama file acak
$targetFile = $targetDir . $randomFileName; // Menggabungkan direktori dengan nama file acak

$uploadOk = 1;

// Cek apakah file gambar adalah gambar
$check = getimagesize($_FILES["LokasiFile"]["tmp_name"]);
if ($check === false) {
    echo "File yang diupload bukan gambar.";
    $uploadOk = 0;
}

// Cek ukuran file
if ($_FILES["LokasiFile"]["size"] > 500000000000000000) { // Maksimal 5MB
    echo "Maaf, ukuran file terlalu besar.";
    $uploadOk = 0;
}

// Cek jenis file
if (!in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif'])) {
    echo "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
    $uploadOk = 0;
}

// Jika semua cek lolos, lakukan upload
if ($uploadOk == 1) {
    if (move_uploaded_file($_FILES["LokasiFile"]["tmp_name"], $targetFile)) {
        // Query untuk menambah foto
        $sql = "INSERT INTO gallery_foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID) VALUES (:judulFoto, :deskripsiFoto, NOW(), :lokasiFile, :albumID, :userId)";
        $stmt = $pdo->prepare($sql);

        // Bind parameter
        $stmt->bindParam(':judulFoto', $judulFoto);
        $stmt->bindParam(':deskripsiFoto', $deskripsiFoto);
        $stmt->bindParam(':lokasiFile', $targetFile);
        $stmt->bindParam(':albumID', $albumID);
        $stmt->bindParam(':userId', $userId);

        // Eksekusi query
        if ($stmt->execute()) {
            header("Location: photos.php?success=Foto berhasil ditambahkan");
            exit();
        } else {
            echo "Gagal menambahkan foto.";
        }
    } else {
        echo "Maaf, terjadi kesalahan saat mengupload file.";
    }
}

}

// Jika ada pencarian
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%'; // Wildcard untuk LIKE
    $search_query = "WHERE f.JudulFoto LIKE :search OR f.DeskripsiFoto LIKE :search OR a.NamaAlbum LIKE :search OR u.Username LIKE :search OR FotoID LIKE :search";
}

// Ambil semua foto dari database dengan JOIN untuk mendapatkan Nama Album dan Username
$sql = "
    SELECT 
        f.FotoID, 
        f.JudulFoto, 
        f.DeskripsiFoto, 
        f.TanggalUnggah, 
        f.LokasiFile, 
        a.NamaAlbum, 
        u.Username 
    FROM 
        gallery_foto f
    JOIN 
        gallery_album a ON f.AlbumID = a.AlbumID
    JOIN 
        gallery_user u ON f.UserID = u.UserID
    $search_query
";
$stmt_photos = $pdo->prepare($sql);

// Jika ada pencarian, bind parameter
if (!empty($search_query)) {
    $stmt_photos->bindParam(':search', $search, PDO::PARAM_STR);
}

$stmt_photos->execute();
$photos = $stmt_photos->fetchAll();

// Ambil semua album untuk dropdown
$sql_albums = "SELECT AlbumID, NamaAlbum FROM gallery_album";
$stmt_albums = $pdo->prepare($sql_albums);
$stmt_albums->execute();
$albums = $stmt_albums->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Foto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            height: 100vh;
            padding-top: 20px;
            transition: width 0.3s;
            position: fixed;
            z-index: 1000;
            overflow: hidden; /* Menghindari scrollbar */
        }

        .sidebar a {
            color: white;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
            opacity: 1;
            transition: opacity 0.3s;
        }

        .sidebar a:hover {
            background-color: #575757;
        }

        .content {
            margin-left: 250px; /* Sesuaikan dengan lebar sidebar */
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s;
        }

        .toggle-sidebar {
            position: absolute;
            left: 15px;
            top: 15px;
            z-index: 1000;
            cursor: pointer;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }

        /* Sidebar ditutup */
        .sidebar.active {
            width: 0; /* Sidebar menyusut ke 0 */
        }

        .sidebar.active a {
            opacity: 0; /* Menyembunyikan menu */
        }

        .content.active {
            margin-left: 0; /* Konten mengambil seluruh lebar */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        @media print {
  /* Sembunyikan semua elemen */
  body * {
    visibility: hidden;
  }

  /* Hanya tampilkan tabel */
  #myTable, #myTable * {
    visibility: visible;
  }

  /* Pastikan tabel tetap pada layout yang benar */
  #myTable {
    position: absolute;
    top: 0;
    left: 0;
  }

  #myAction, #myAction * {
    visibility: visible;
  }

  /* Pastikan tabel tetap pada layout yang benar */
  #myAction {
    position: absolute;
    top: 0;
    left: 0;
  }
  
  #myAction, #myAction * {
    visibility: visible;
  }

  /* Pastikan tabel tetap pada layout yang benar */
  #myAction {
    position: absolute;
    top: 0;
    left: 0;
  }

}
    </style>
</head>
<body>

<?php require 'dashboard-navbar.php' ?>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none text-muted"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Foto</li>
            </ol>
        </nav>
        <h2>Data Foto</h2>

        <!-- Tombol untuk membuka modal tambah foto -->
         <div class="mb-2">
            <button class="btn btn-primary" data-toggle="modal" data-target="#addPhotoModal"><i class="fas fa-plus"></i> Tambah Foto</button>
            <button class="btn btn-info " onClick="window.print()"><i class="fas fa-print"></i> Cetak</button>
        </div>
        <!-- Modal untuk tambah foto -->
        <div class="modal fade" id="addPhotoModal" tabindex="-1" role="dialog" aria-labelledby="addPhotoModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPhotoModalLabel">Tambah Foto Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_photo"> <!-- Indikator aksi -->
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="JudulFoto">Judul Foto</label>
                                <input type="text" class="form-control" id="JudulFoto" name="JudulFoto" required>
                            </div>
                            <div class="form-group">
                                <label for="DeskripsiFoto">Deskripsi</label>
                                <textarea class="form-control" id="DeskripsiFoto" name="DeskripsiFoto" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="AlbumID">Album</label>
                                <select class="form-control" id="AlbumID" name="AlbumID" required>
                                    <option value="">Pilih Album</option>
                                    <?php foreach ($albums as $album): ?>
                                        <option value="<?php echo $album['AlbumID']; ?>"><?php echo htmlspecialchars($album['NamaAlbum']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="LokasiFile">Gambar</label>
                                <input type="file" class="form-control" id="LokasiFile" name="LokasiFile" accept="image/*" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Tambah Foto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Pencarian -->
        <form method="get" action="">
            <input type="text" name="search" placeholder="Cari judul, deskripsi, album, atau username..." class="form-control d-inline-block" style="width: 70%;" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <table id="myTable">
            <thead>
                <tr>
                    <th class="text-center">ID Foto</th>
                    <th class="text-center">Judul Foto</th>
                    <th class="text-center">Deskripsi</th>
                    <th class="text-center">Tanggal Unggah</th>
                    <th class="text-center">Gambar</th>
                    <th class="text-center">Nama Album</th>
                    <th class="text-center">Username</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($photos) > 0): ?>
                    <?php foreach ($photos as $photo): ?>
                    <tr>
                        <td class="text-center"><?php echo htmlspecialchars($photo['FotoID']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($photo['JudulFoto']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($photo['DeskripsiFoto']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($photo['TanggalUnggah']); ?></td>
                        <td class="text-center">
                            <img src="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" alt="Gambar Foto" style="width: 100px; height: 66px; border-radius: 5px;">
                        </td>
                        <td class="text-center"><?php echo htmlspecialchars($photo['NamaAlbum']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($photo['Username']); ?></td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="edit_photos.php?FotoID=<?php echo $photo['FotoID']; ?>" class="btn btn-warning btn-sm"><i class="fa-solid fa-pen-to-square"></i></a>
                                <a href="delete_photos.php?FotoID=<?php echo $photo['FotoID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?');"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Tidak ada hasil untuk pencarian "<?php echo htmlspecialchars($_GET['search']); ?>"</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('active');
        }
    </script>
</body>
</html>
