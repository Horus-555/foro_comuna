<?php
// app/functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function flash_set($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_show() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $class = $f['type'] === 'error' ? 'alert error' : 'alert success';
        echo "<div class=\"$class\">".e($f['msg'])."</div>";
        unset($_SESSION['flash']);
    }
}
