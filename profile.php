<?php
// Bonus: halaman edit profil
require 'config.php';
requireLogin();

$user = findUserById($_SESSION['user_id']);
if (!$user) {
    session_destroy();
    redirect('login.php');
}

$errors = [];
$name = $user['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean($_POST['name'] ?? '');
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if ($name === '' || mb_strlen($name) < 3) {
        $errors[] = 'Nama wajib diisi, minimal 3 karakter.';
    }

    // Ganti password hanya jika kolom password baru diisi
    $changePassword = ($newPassword !== '');
    if ($changePassword) {
        if (!password_verify($oldPassword, $user['password'])) {
            $errors[] = 'Password lama salah.';
        }
        if (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }
    }

    if (empty($errors)) {
        $users = getUsers();
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['name'] = $name;
                if ($changePassword) {
                    $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
            }
        }
        unset($u);

        if (saveUsers($users)) {
            $_SESSION['user_name'] = $name;
            setFlash('success', 'Profil berhasil diperbarui.');
            redirect('dashboard.php');
        } else {
            $errors[] = 'Gagal menyimpan perubahan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="card">
    <h1>Edit profil</h1>
    <p class="subtitle">Kosongkan kolom password jika tidak ingin menggantinya.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="profile.php" novalidate>
        <label for="name">Nama lengkap</label>
        <input type="text" id="name" name="name" value="<?= e($name) ?>">

        <label for="email">Email (tidak dapat diubah)</label>
        <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>

        <label for="old_password">Password lama</label>
        <input type="password" id="old_password" name="old_password" placeholder="Isi jika ingin ganti password">

        <label for="new_password">Password baru</label>
        <input type="password" id="new_password" name="new_password" placeholder="Minimal 6 karakter">

        <button type="submit" class="btn">Simpan perubahan</button>
    </form>

    <p class="switch"><a href="dashboard.php">Kembali ke dashboard</a></p>
</main>
</body>
</html>
