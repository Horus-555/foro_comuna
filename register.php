<?php
session_start();
require_once "app/db.php";
require_once "app/auth.php";
require_once "app/functions.php";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $rut = trim($_POST['rut']);
    $clave = $_POST['password'];
    $clave2 = $_POST['password2'];

    if ($clave !== $clave2) {
        $error = "Las contraseñas no coinciden.";
    } else {

        // Validar si el rut ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE rut = ? LIMIT 1");
        $stmt->execute([$rut]);

        if ($stmt->fetch()) {
            $error = "El RUT ya está registrado.";
        } else {

            $hash = password_hash($clave, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, rut, clave, rol)
                                   VALUES (?, ?, ?, ?, 'usuario')");

            if ($stmt->execute([$nombre, $apellido, $rut, $hash])) {
                $success = "Usuario registrado correctamente. Ahora puedes iniciar sesión.";
            } else {
                $error = "Error al registrar usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/header.php"; ?>

<div class="login-box">

    <h2>Registro</h2>

    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert success"><?= e($success) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">

        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Apellido</label>
        <input type="text" name="apellido" required>

        <label>RUT</label>
        <input type="text" name="rut" required>

        <label>Contraseña</label>
        <input type="password" name="password" required>

        <label>Repetir contraseña</label>
        <input type="password" name="password2" required>

        <button type="submit">Registrarse</button>

        <div class="register-section">
        <a href="login.php" class="register-btn">¿Ya tienes cuenta? <strong>Inicia sesion</strong></a>
    </form>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
