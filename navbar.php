<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="gallery.php">Galeri Foto</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto d-flex align-items-center">
            <li class="nav-item mr-3">
                <!-- Search form -->
                <form method="post" action="gallery.php" class="form-inline">
                    <div class="input-group input-group-md ">
                        <input type="text" class="form-control rounded-0" name="search" placeholder="Cari Judul Foto"  autocomplete="off">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-dark rounded-0"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </div>
                </form>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="album.php">Album</a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_level'] === 'Admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-dashboard.php">Admin</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>