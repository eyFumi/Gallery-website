<?php
require 'config.php';
session_start();

// Periksa apakah foto_id ada di URL
if (!isset($_GET['foto_id'])) {
    header("Location: gallery.php"); // Kembali ke galeri jika tidak ada foto_id
    exit();
}

$foto_id = $_GET['foto_id'];

// Ambil detail foto berdasarkan foto_id
$stmt_photo = $pdo->prepare("SELECT g.*, u.username FROM gallery_foto g JOIN gallery_user u ON g.UserID = u.UserID WHERE g.FotoID = ?");
$stmt_photo->execute([$foto_id]);
$photo = $stmt_photo->fetch();

// Jika foto tidak ditemukan
if (!$photo) {
    header("Location: gallery.php"); // Kembali ke galeri jika foto tidak ditemukan
    exit();
}

// Proses komentar
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isi_komentar'])) {
    $isi_komentar = $_POST['isi_komentar'];

    // Tambahkan komentar jika pengguna sudah login
    if ($user_id) {
        $stmt_komentar = $pdo->prepare("INSERT INTO gallery_komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES (?, ?, ?, NOW())");
        $stmt_komentar->execute([$foto_id, $user_id, $isi_komentar]);

        // Segarkan halaman setelah komentar ditambahkan
        header("Location: detail_photo.php?foto_id=$foto_id");
        exit();
    }
}

// Ambil komentar untuk foto
$stmt_comments = $pdo->prepare("SELECT c.*, u.username FROM gallery_komentarfoto c JOIN gallery_user u ON c.UserID = u.UserID WHERE c.FotoID = ?");
$stmt_comments->execute([$foto_id]);
$comments = $stmt_comments->fetchAll();

// Ambil jumlah like untuk foto ini
$stmt_likes_count = $pdo->prepare("SELECT COUNT(*) FROM gallery_likesfoto WHERE FotoID = ?");
$stmt_likes_count->execute([$foto_id]);
$likes_count = $stmt_likes_count->fetchColumn();

// Proses like/dislike
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (isset($_SESSION['user_id'])) {
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

        // Segarkan halaman setelah like/dislike
        header("Location: detail_photo.php?foto_id=$foto_id");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Foto</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function checkLoginAndProceed(formId, action) {
            var isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
            if (!isLoggedIn) {
                alert("Kamu harus login dahulu untuk menggunakan fitur like dan komen.");
                window.location.href = "login.php"; // Arahkan ke halaman login
            } else {
                // Jika sudah login, set action dan submit form
                document.getElementById('likeAction').value = action;
                document.getElementById(formId).submit();
            }
        }

    </script>
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="container mt-4">
    <a href="gallery.php" class="btn btn-secondary mt-3"><i class="fa-solid fa-circle-left"></i></a>
    <div class="row mt-3">
        <div class="col-md-6">
            <img src="<?php echo htmlspecialchars($photo['LokasiFile']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($photo['JudulFoto']); ?>" style="width:540px; height:360px; object-fit:cover; object-position:center;">
            <h5>Likes: <?php echo $likes_count; ?></h5>
            <form id="likeForm" method="post" class="mb-3">
                <input type="hidden" name="foto_id" value="<?php echo $foto_id; ?>">
                <input type="hidden" name="action" id="likeAction" value="">
                <button type="button" onclick="checkLoginAndProceed('likeForm', 'like')" class="btn btn-primary"><i class="fas fa-thumbs-up"></i></button>
                <button type="button" onclick="checkLoginAndProceed('likeForm', 'dislike')" class="btn btn-danger"><i class="fas fa-thumbs-down"></i></button>
            </form>

        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($photo['JudulFoto']); ?></h2>    
            <p><?php echo htmlspecialchars($photo['DeskripsiFoto']); ?></p>
            <p><small class="text-muted">Diupload oleh <?php echo htmlspecialchars($photo['username']); ?> pada <?php echo date('d M Y', strtotime($photo['TanggalUnggah'])); ?></small></p>

            <h5>Komentar:</h5>
            <form id="commentForm" method="post" class="mb-3">
                <input type="text" name="isi_komentar" class="form-control" placeholder="Tulis komentar..." autocomplete="off" required>
                <button type="button" onclick="checkLoginAndProceed('commentForm')" class="btn btn-primary mt-2">Kirim</button>
            </form>

            <div>
                <?php foreach ($comments as $comment): ?>
                    <div class="border p-2 mb-1">
                        <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                        <p><?php echo htmlspecialchars($comment['IsiKomentar']); ?></p>
                        <small class="text-muted"><?php echo date('d M Y H:i', strtotime($comment['TanggalKomentar'])); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
