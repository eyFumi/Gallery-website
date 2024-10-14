<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Navbar</title>
    <link rel="stylesheet" href="styles.css"> <!-- Tambahkan CSS jika diperlukan -->
    <style>
        /* Contoh gaya untuk navbar */
        body {
            font-family: Arial, sans-serif;
        }
        nav {
            background-color: #333;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
        }
        nav a:hover {
            background-color: #575757;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav>
        <div>
        <nav>
        <div>
            <a href="gallery.php">Home</a>
            <a href="album.php">Album</a> <!-- Tambahkan link ke galeri -->
            <a href="admin-dashboard.php">admin</a> <!-- Tambahkan link ke galeri -->
            <a href="logout.php">logout</a> <!-- Tambahkan link ke galeri -->

        </div>
    </nav>
            
        </div>
        <div>
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <h1>Selamat Datang di Galeri</h1>
        <p>Ini adalah halaman utama dari aplikasi galeri Anda.</p>
    </div>
</body>
</html>
