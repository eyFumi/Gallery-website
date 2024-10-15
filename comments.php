<?php
require 'config.php';
session_start();

// Cek apakah pengguna sudah login dan berlevel admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_level'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Jika ada pencarian
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%'; // Wildcard untuk LIKE
    $search_query = "WHERE f.JudulFoto LIKE :search OR u.Username LIKE :search OR k.IsiKomentar LIKE :search OR KomentarID LIKE :search";
}

// Ambil semua komentar foto dari database dengan JOIN untuk mendapatkan Username dan Judul Foto
$sql = "
    SELECT 
        k.KomentarID, 
        k.FotoID, 
        k.UserID, 
        k.IsiKomentar, 
        k.TanggalKomentar, 
        f.JudulFoto, 
        u.Username 
    FROM 
        gallery_komentarfoto k
    JOIN 
        gallery_foto f ON k.FotoID = f.FotoID
    JOIN 
        gallery_user u ON k.UserID = u.UserID
    $search_query
";
$stmt_comments = $pdo->prepare($sql);

// Jika ada pencarian, bind parameter
if (!empty($search_query)) {
    $stmt_comments->bindParam(':search', $search, PDO::PARAM_STR);
}

$stmt_comments->execute();
$comments = $stmt_comments->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Komentar Foto</title>
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
                <li class="breadcrumb-item active" aria-current="page">Komentar Foto</li>
            </ol>
        </nav>
        
            <h2>Data Komentar Foto</h2>

            <button class="btn btn-info mb-2" onClick="window.print()"><i class="fas fa-print"></i> Cetak</button>

            <!-- Form Pencarian -->
            <form method="get" action="">
                <input type="text" name="search" placeholder="Cari judul, username, atau komentar..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" class="form-control d-inline-block" style="width: 70%;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>

            <table id="myTable">
                <thead>
                    <tr>
                        <th class="text-center">ID Komentar</th>
                        <th class="text-center">Judul Foto</th>
                        <th class="text-center">Username</th>
                        <th class="text-center">Isi Komentar</th>
                        <th class="text-center">Tanggal Komentar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td class="text-center"><?php echo htmlspecialchars($comment['KomentarID']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($comment['JudulFoto']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($comment['Username']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($comment['IsiKomentar']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($comment['TanggalKomentar']); ?></td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <a href="edit_comment.php?KomentarID=<?php echo $comment['KomentarID']; ?>" class="btn btn-warning"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="delete_comment.php?KomentarID=<?php echo $comment['KomentarID']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');"><i class="fa-solid fa-trash"></i></a>
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
        </div>


    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.content').classList.toggle('active');
        }
    </script>
</body>
</html>
