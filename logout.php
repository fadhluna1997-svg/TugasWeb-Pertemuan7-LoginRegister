<?php
require 'config.php';

// Hapus token remember me (jika ada)
if (isset($_SESSION['user_id'])) {
    setRememberToken($_SESSION['user_id'], '');
}
setcookie('remember_me', '', time() - 3600, '/');

// Hancurkan session (Requirement #8)
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Mulai session baru hanya untuk menampilkan pesan sukses
session_start();
setFlash('success', 'Kamu berhasil logout.');
redirect('login.php');
