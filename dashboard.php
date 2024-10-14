<?php
session_start(); // Memulai sesi

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') { 
    header("Location: login.php");
    exit();
}



require 'config.php'; // Koneksi menggunakan PDO

// Menghitung total data user
$queryUser = $pdo->query("SELECT COUNT(*) as total FROM gallery_user");
$jumlahUser = $queryUser->fetchColumn();

// Menghitung total data album
$queryAlbum = $pdo->query("SELECT COUNT(*) as total FROM gallery_album");
$jumlahAlbum = $queryAlbum->fetchColumn();

// Menghitung total data foto
$queryFoto = $pdo->query("SELECT COUNT(*) as total FROM gallery_foto");
$jumlahFoto = $queryFoto->fetchColumn();

// Menghitung total komentar
$queryKomentar = $pdo->query("SELECT COUNT(*) as total FROM gallery_komentarfoto");
$jumlahKomentar = $queryKomentar->fetchColumn();

// Menghitung total likes
$queryLikes = $pdo->query("SELECT COUNT(*) as total FROM gallery_likesfoto");
$jumlahLikes = $queryLikes->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Dashboard Admin</title>
</head>

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

        .kotak{
        border: solid;
    }

    .summary-kategori{
        background-color: white;
    }
    .summary {
        font-size: 1.3rem;
        color: #343a40;
        font-weight: bold;
    }
    .icon-large {
        font-size: 3rem;
        color: #007bff;
    }
    .kotak p {
        margin: 10px 0;
        color: #6c757d;
    }
    .kotak a {
        color: #007bff;
        text-decoration: none;
        font-weight: bold;
    }
    .kotak a:hover {
        text-decoration: underline;
    }
</style>

<body>
<?php require 'dashboard-navbar.php' ?>

        <div class="container mt-5">
        

        <div class="row mt-4">
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="kotak p-3">
                    <div class="row">
                        <div class="col-6 text-center">
                            <i class="fas fa-users fa-6x"></i>
                        </div>
                        <div class="col-6">
                            <p class="summary">User</p>
                            <p><?php echo $jumlahUser; ?> User</p>
                            <p><a href="admin-dashboard.php " class="text-dark text-decoration-none">Lihat Detail</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="kotak p-3">
                    <div class="row">
                        <div class="col-6 text-center">
                            <i class="fas fa-photo-video fa-6x"></i>
                        </div>
                        <div class="col-6">
                            <p class="summary">Album</p>
                            <p><?php echo $jumlahAlbum; ?> Album</p>
                            <p><a href="albums.php" class="text-dark text-decoration-none">Lihat Detail</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="kotak p-3">
                    <div class="row">
                        <div class="col-6 text-center">
                            <i class="fas fa-images fa-6x"></i>
                        </div>
                        <div class="col-6">
                            <p class="summary">Foto</p>
                            <p><?php echo $jumlahFoto; ?> Foto</p>
                            <p><a href="photos.php" class="text-dark text-decoration-none">Lihat Detail</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="kotak p-3">
                    <div class="row">
                        <div class="col-6 text-center">
                            <i class="fas fa-comments fa-6x"></i>
                        </div>
                        <div class="col-6">
                            <p class="summary">Komentar</p>
                            <p><?php echo $jumlahKomentar; ?> Komentar</p>
                            <p><a href="comments.php" class="text-dark text-decoration-none">Lihat Detail</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="kotak p-3">
                    <div class="row">
                        <div class="col-6 text-center">
                            <i class="fas fa-heart fa-6x"></i>
                        </div>
                        <div class="col-6">
                            <p class="summary">Likes</p>
                            <p><?php echo $jumlahLikes; ?> Likes</p>
                            <p><a href="likes.php" class="text-dark text-decoration-none">Lihat Detail</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('active');
        }
    </script>
    
    <!-- Menggunakan link CDN online untuk Bootstrap JavaScript -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Menggunakan link CDN online untuk FontAwesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>
