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
    $search = '%' . $_GET['search'] . '%'; // Gunakan wildcard untuk LIKE
    $search_query = "WHERE UserID LIKE :search OR Username LIKE :search OR Email LIKE :search OR NamaLengkap LIKE :search OR Alamat LIKE :search OR Level LIKE :search";
}

// Ambil semua pengguna atau pengguna berdasarkan pencarian
$sql = "SELECT * FROM gallery_user $search_query";
$stmt_users = $pdo->prepare($sql);

// Jika ada pencarian, bind parameter
if (!empty($search_query)) {
    $stmt_users->bindParam(':search', $search, PDO::PARAM_STR);
}

$stmt_users->execute();
$users = $stmt_users->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User</title>
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
    </style>
</head>
<body>

    <?php require 'dashboard-navbar.php' ?>

        <h2>Data User</h2>

        <!-- Form Pencarian -->
        <form method="get" action="">
            <input type="text" name="search" placeholder="Cari pengguna..." class="form-control d-inline-block" style="width: 70%;" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <!-- Tabel Data Pengguna -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Nama Lengkap</th>
                    <th>Alamat</th>
                    <th>Level</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                        <td><?php echo htmlspecialchars($user['Username']); ?></td>
                        <td><?php echo htmlspecialchars($user['Email']); ?></td>
                        <td><?php echo htmlspecialchars($user['NamaLengkap']); ?></td>
                        <td><?php echo htmlspecialchars($user['Alamat']); ?></td>
                        <td><?php echo htmlspecialchars($user['Level']); ?></td>
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
