<?php
session_start();
require_once "app/db.php";
require_once "app/auth.php";
require_once "app/functions.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = trim($_POST['rut']);
    $clave = $_POST['password'];

    // Obtener usuario incluyendo el campo "role"
    $stmt = $pdo->prepare("
        SELECT id, nombre, apellido, rut, email, clave, role
        FROM usuarios
        WHERE rut = ?
        LIMIT 1
    ");
    $stmt->execute([$rut]);

    // Asegurar que el fetch sea asociativo
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($clave, $user['clave'])) {
        loginUser($user); // Guarda role correctamente en la sesión
        header("Location: index.php");
        exit;
    } else {
        $error = "RUT o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Iniciar sesión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "includes/header.php"; ?>

<div class="login-box">

  <h2>Iniciar sesión</h2>

  <?php if ($error): ?>
      <div class="alert error"><?= e($error) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <label>RUT</label>
    <input type="text" name="rut" required>

    <label>Contraseña</label>
    <input type="password" name="password" required>

    <button type="submit">Iniciar sesion</button>
  </form>

  <div class="register-section">
    <a href="register.php" class="register-btn">¿No tienes cuenta? <strong>Regístrate</strong></a>
  </div>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
