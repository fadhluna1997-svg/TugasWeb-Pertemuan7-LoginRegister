<?php
// ============================================================
// config.php - Konfigurasi & fungsi bantu (dipakai semua halaman)
// ============================================================
session_start();

define('USERS_FILE', __DIR__ . '/data/users.json');
define('REMEMBER_DAYS', 30);

// ---------- Sanitasi input (Requirement #9) ----------
function clean($value) {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// Escape saat menampilkan output (double_encode=false agar tidak ter-encode dua kali)
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8', false);
}

// ---------- Penyimpanan JSON (Requirement #4) ----------
function getUsers() {
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, '[]');
    }
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

function saveUsers($users) {
    $json = json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents(USERS_FILE, $json, LOCK_EX) !== false;
}

function findUserByEmail($email) {
    foreach (getUsers() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

function findUserById($id) {
    foreach (getUsers() as $user) {
        if ($user['id'] === $id) {
            return $user;
        }
    }
    return null;
}

// ---------- Flash message (Requirement #10) ----------
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['message']) . '</div>';
    }
}

function redirect($page) {
    header('Location: ' . $page);
    exit;
}

// ---------- Session & Remember Me (Bonus) ----------
function loginUser($user) {
    session_regenerate_id(true); // cegah session fixation
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
}

function autoLoginFromCookie() {
    if (isset($_SESSION['user_id']) || empty($_COOKIE['remember_me'])) {
        return;
    }
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) {
        return;
    }
    list($id, $token) = $parts;
    $user = findUserById($id);
    if ($user && !empty($user['remember_token'])
        && hash_equals($user['remember_token'], hash('sha256', $token))) {
        loginUser($user);
    }
}

function setRememberToken($userId, $token) {
    $users = getUsers();
    foreach ($users as &$u) {
        if ($u['id'] === $userId) {
            $u['remember_token'] = $token ? hash('sha256', $token) : '';
        }
    }
    unset($u);
    saveUsers($users);
}

autoLoginFromCookie();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Dipanggil di halaman yang diproteksi (Requirement #7)
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        redirect('login.php');
    }
}
