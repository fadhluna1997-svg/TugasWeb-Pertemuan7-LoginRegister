<?php
require 'config.php';
requireLogin(); // redirect ke login jika belum login (Requirement #7)

$user = findUserById($_SESSION['user_id']);
if (!$user) { // data user sudah tidak ada di JSON
    session_destroy();
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="card wide">
    <?php showFlash(); ?>

    <h1>Halo, <?= e($user['name']) ?> 👋</h1>
    <p class="subtitle">Kamu berhasil masuk ke halaman yang diproteksi.</p>

    <dl class="info">
        <dt>Nama</dt>
        <dd><?= e($user['name']) ?></dd>
        <dt>Email</dt>
        <dd><?= e($user['email']) ?></dd>
        <dt>Terdaftar sejak</dt>
        <dd><?= e($user['created_at']) ?></dd>
    </dl>

    <div class="actions">
        <a href="profile.php" class="btn btn-outline">Edit profil</a>
        <a href="logout.php" class="btn">Logout</a>
    </div>
</main>
</body>
</html>
