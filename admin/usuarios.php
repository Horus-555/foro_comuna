<?php
session_start();
require_once "../app/db.php";
require_once "../app/auth.php";
require_once "../app/functions.php";

if (!isAdmin()) { header("Location: ../index.php"); exit; }

// Toggle role admin <-> usuario
if (isset($_GET['toggle_role'])) {
    $id = intval($_GET['toggle_role']);
    $u = $pdo->prepare("SELECT role FROM usuarios WHERE id = ?");
    $u->execute([$id]);
    $row = $u->fetch();

    if ($row) {
        $new = $row['role'] === 'admin' ? 'usuario' : 'admin';
        $pdo->prepare("UPDATE usuarios SET role = ? WHERE id = ?")->execute([$new, $id]);
    }
    header("Location: usuarios.php");
    exit;
}

$users = $pdo->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin - Usuarios</title>
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

        <div class="admin-header">
            <h1>Gestión de Usuarios</h1>
        </div>

        <table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>RUT</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>

            <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['id']) ?></td>
                <td><?= e($u['nombre'] . ' ' . $u['apellido']) ?></td>
                <td><?= e($u['rut']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><strong><?= e(ucfirst($u['role'])) ?></strong></td>

                <td>
                    <a href="usuario_edit.php?id=<?= e($u['id']) ?>" class="btn-small btn-blue">Editar o eliminar</a>
                    <a href="?toggle_role=<?= e($u['id']) ?>" class="btn-small btn-green">Cambiar Rol</a>
                </td>
            </tr>
            <?php endforeach; ?>

        </table>

    </main>
</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
