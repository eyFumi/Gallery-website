<?php
session_start();
session_destroy(); // Mengakhiri semua sesi
header("Location: gallery.php"); // Mengarahkan kembali ke halaman login
exit();
?>
