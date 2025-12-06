<?php
// app/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

/*
 |----------------------------------------
 |  Verifica si hay sesión activa
 |----------------------------------------
*/
function isLoggedIn(): bool {
    return isset($_SESSION['usuario_id']);
}

/*
 |----------------------------------------
 |  Verifica si el usuario es admin
 |----------------------------------------
*/
function isAdmin(): bool {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/*
 |----------------------------------------
 |  Obliga a iniciar sesión
 |----------------------------------------
*/
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ./login.php");
        exit;
    }
}

/*
 |----------------------------------------
 |  Guardar sesión del usuario
 |----------------------------------------
*/
function loginUser($user) {
    $_SESSION['usuario_id']       = $user['id'];
    $_SESSION['usuario_rut']      = $user['rut'];
    $_SESSION['usuario_nombre']   = $user['nombre'];
    $_SESSION['usuario_apellido'] = $user['apellido'];
    $_SESSION['usuario_rol']      = $user['role'] ?? 'usuario';
}

/*
 |----------------------------------------
 |  Cerrar sesión
 |----------------------------------------
*/
function logoutUser() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/*
 |----------------------------------------
 |  Obtener usuario actual
 |----------------------------------------
*/
function currentUser(): ?array {
    if (!isLoggedIn()) return null;

    return [
        'id'       => $_SESSION['usuario_id'],
        'nombre'   => $_SESSION['usuario_nombre'],
        'apellido' => $_SESSION['usuario_apellido'],
        'rol'      => $_SESSION['usuario_rol'] ?? 'usuario'
    ];
}
