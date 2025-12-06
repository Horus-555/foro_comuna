<?php
require_once "../app/auth.php";
require_once "../app/functions.php";

// Bloqueo si no es admin
if (!isAdmin()) {
    header("Location: ../index.php");
    exit;
}

$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Administración</title>

<!-- Usa el CSS general del sitio -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<link rel="stylesheet" href="../admin/assets/css/style.css">

</head>
<body>

<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <h2>Admin Panel</h2>

        <ul>
            <li><a href="../index.php">🏠 Volver al Sitio</a></li>
            <li><a href="usuarios.php">👤 Usuarios</a></li>
            <li><a href="post.php">📝 Publicaciones</a></li>
            <li><a href="categorias.php">📂 Categorías</a></li>
        </ul>
    </aside>

    <!-- Contenido -->
    <main class="admin-content">
        <div class="admin-card">
            <h1 class="admin-title">Panel de Administración</h1>

            <p class="welcome">
                Bienvenido, <strong><?= e($user['nombre']) . " " . e($user['apellido']) ?></strong>
            </p>

            <p>Desde aquí puedes gestionar usuarios, publicaciones y categorías.</p>
        </div>
    </main>

</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
