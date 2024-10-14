<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hashing password
    $nama_lengkap = $_POST['nama_lengkap'];
    $alamat = $_POST['alamat'];
    $level = $_POST['level'];

    // Periksa apakah username atau email sudah ada di database
    $stmt = $pdo->prepare("SELECT * FROM gallery_user WHERE Username = ? OR Email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        // Jika username atau email sudah ada, tampilkan pesan kesalahan
        $user = $stmt->fetch();
        if ($user['Username'] === $username) {
            echo "<div class='alert alert-danger'>Username sudah digunakan!</div>";
        } elseif ($user['Email'] === $email) {
            echo "<div class='alert alert-danger'>Email sudah digunakan!</div>";
        }
    } else {
        // Jika username dan email belum ada, lakukan registrasi
        $stmt = $pdo->prepare("INSERT INTO gallery_user (Username, Email, Password, NamaLengkap, Alamat, Level) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $password, $nama_lengkap, $alamat, $level])) {
            echo "<div class='alert alert-success'>Registrasi berhasil! Silakan <a href='login.php'>login</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Registrasi gagal!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h2 class="text-center mb-4">Registrasi</h2>
                    <form method="POST">
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user"></i> Username</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="nama_lengkap"><i class="fas fa-user-circle"></i> Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="alamat"><i class="fas fa-home"></i> Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" placeholder="Masukkan alamat" required autocomplete="off"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="level"><i class="fas fa-user-tag"></i> Pilih Level</label>
                            <select class="form-control" id="level" name="level" required>
                                <option value="User">User</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Daftar</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php">Sudah punya akun? Login di sini.</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
