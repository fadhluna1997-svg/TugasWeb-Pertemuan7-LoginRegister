<?php
require 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        $errors[] = 'Email dan password wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $user = findUserByEmail($email);

        // password_verify membandingkan password dengan hash (Requirement #3 & #6)
        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);

            // Bonus: Remember Me dengan cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setRememberToken($user['id'], $token);
                setcookie('remember_me', $user['id'] . ':' . $token, [
                    'expires'  => time() + (86400 * REMEMBER_DAYS),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            setFlash('success', 'Login berhasil! Selamat datang, ' . $user['name'] . '.');
            redirect('dashboard.php');
        } else {
            // Pesan sengaja dibuat umum agar tidak membocorkan email mana yang terdaftar
            $errors[] = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="card">
    <h1>Selamat datang kembali</h1>
    <p class="subtitle">Masuk untuk membuka dashboard kamu.</p>

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

    <form method="POST" action="login.php" novalidate>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="nama@email.com">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password kamu">

        <label class="check">
            <input type="checkbox" name="remember"> Ingat saya selama <?= REMEMBER_DAYS ?> hari
        </label>

        <button type="submit" class="btn">Login</button>
    </form>

    <p class="switch">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
</main>
</body>
</html>
