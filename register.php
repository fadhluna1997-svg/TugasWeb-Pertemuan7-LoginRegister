<?php
require 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi input (Requirement #9). Password TIDAK disanitasi agar tidak berubah.
    $name     = clean($_POST['name'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validasi (Requirement #1)
    if ($name === '') {
        $errors[] = 'Nama wajib diisi.';
    } elseif (mb_strlen($name) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    // Validasi email dengan filter_var (Requirement #2)
    if ($email === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if ($password === '') {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // Cek duplikasi email (Requirement #5)
    if (empty($errors) && findUserByEmail($email) !== null) {
        $errors[] = 'Email sudah terdaftar. Gunakan email lain atau login.';
    }

    // Simpan user baru
    if (empty($errors)) {
        $users = getUsers();
        $users[] = [
            'id'             => uniqid('usr_', true),
            'name'           => $name,
            'email'          => strtolower($email),
            'password'       => password_hash($password, PASSWORD_DEFAULT), // Requirement #3
            'remember_token' => '',
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        if (saveUsers($users)) {
            setFlash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        } else {
            $errors[] = 'Gagal menyimpan data. Periksa izin folder data/.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="card">
    <h1>Buat akun baru</h1>
    <p class="subtitle">Isi data di bawah untuk mulai menggunakan aplikasi.</p>

    <?php showFlash(); ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
        <label for="name">Nama lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>" placeholder="Nama kamu">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter">

        <label for="confirm_password">Konfirmasi password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password">

        <button type="submit" class="btn">Daftar</button>
    </form>

    <p class="switch">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</main>
</body>
</html>
