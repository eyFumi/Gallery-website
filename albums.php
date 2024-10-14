<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') { 
    header("Location: login.php");
    exit();
}

// Proses untuk menambah album
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_album') {
    $namaAlbum = $_POST['NamaAlbum'];
    $deskripsi = $_POST['Deskripsi'];
    $userId = $_SESSION['user_id']; // Ambil ID user dari sesi

    // Query untuk menambah album, tanpa memasukkan TanggalDibuat secara manual
    $sql = "INSERT INTO gallery_album (NamaAlbum, Deskripsi, UserID, TanggalDibuat) VALUES (:namaAlbum, :deskripsi, :userId, NOW())";
    $stmt = $pdo->prepare($sql);
    
    // Bind parameter
    $stmt->bindParam(':namaAlbum', $namaAlbum);
    $stmt->bindParam(':deskripsi', $deskripsi);
    $stmt->bindParam(':userId', $userId);
    
    // Eksekusi query
    if ($stmt->execute()) {
        header("Location: dashboard.php?success=Album berhasil ditambahkan");
        exit();
    } else {
        header("Location: dashboard.php?error=Gagal menambahkan album");
        exit();
    }
}

// Jika ada pencarian
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%'; // Gunakan wildcard untuk LIKE
    $search_query = "WHERE a.NamaAlbum LIKE :search OR a.Deskripsi LIKE :search OR u.Username LIKE :search";
}

// Ambil semua album dari database dengan JOIN untuk mendapatkan Username
$sql = "
    SELECT 
        a.AlbumID, 
        a.NamaAlbum, 
        a.Deskripsi, 
        a.TanggalDibuat, 
        u.Username 
    FROM 
        gallery_album a
    JOIN 
        gallery_user u ON a.UserID = u.UserID
    $search_query
";
$stmt_albums = $pdo->prepare($sql);

// Jika ada pencarian, bind parameter
if (!empty($search_query)) {
    $stmt_albums->bindParam(':search', $search, PDO::PARAM_STR);
}

$stmt_albums->execute();
$albums = $stmt_albums->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Album</title>
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
            position: fixed;
            z-index: 1000;
            overflow: hidden; /* Menghindari scrollbar */
            transition: width 0.3s;
        }

        .sidebar.active {
            width: 0; /* Sidebar menyusut ke 0 */
        }

        .sidebar a {
            color: white;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
            opacity: 1;
            transition: opacity 0.3s;
        }

        .sidebar.active a {
            opacity: 0; /* Menyembunyikan menu */
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

        .content.active {
            margin-left: 0; /* Konten mengambil seluruh lebar */
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

    </style>
</head>
<body>
<?php require 'dashboard-navbar.php' ?>

        <h2>Data Album</h2>

        <!-- Tombol untuk membuka modal tambah album -->
        <button class="btn btn-success mb-2" data-toggle="modal" data-target="#addAlbumModal">Tambah Album</button>

        <!-- Modal untuk tambah album -->
        <div class="modal fade" id="addAlbumModal" tabindex="-1" role="dialog" aria-labelledby="addAlbumModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAlbumModalLabel">Tambah Album Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="add_album"> <!-- Indikator aksi -->
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="albumName">Nama Album</label>
                                <input type="text" class="form-control" id="albumName" name="NamaAlbum" required>
                            </div>
                            <div class="form-group">
                                <label for="albumDescription">Deskripsi</label>
                                <textarea class="form-control" id="albumDescription" name="Deskripsi" rows="3" required></textarea>
                            </div>
                            <!-- Input untuk tanggal sudah dihapus -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Form Pencarian -->
        <form method="get" action="">
            <input type="text" name="search" placeholder="Cari album, deskripsi, atau username..." class="form-control d-inline-block" style="width: 70%;" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID Album</th>
                    <th>Nama Album</th>
                    <th>Deskripsi Album</th>
                    <th>Tanggal Buat</th>
                    <th>Username</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($albums) > 0): ?>
                    <?php foreach ($albums as $album): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($album['AlbumID']); ?></td>
                        <td><?php echo htmlspecialchars($album['NamaAlbum']); ?></td>
                        <td><?php echo htmlspecialchars($album['Deskripsi']); ?></td>
                        <td><?php echo htmlspecialchars($album['TanggalDibuat']); ?></td>
                        <td><?php echo htmlspecialchars($album['Username']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_album.php?AlbumID=<?php echo $album['AlbumID']; ?>" class="btn btn-warning">Edit</a>
                                <a href="delete_album.php?AlbumID=<?php echo $album['AlbumID']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus album ini beserta foto-fotonya?');">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Tidak ada hasil untuk pencarian "<?php echo htmlspecialchars($_GET['search']); ?>"</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

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
