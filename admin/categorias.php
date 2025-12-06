<?php
session_start();
require_once "../app/db.php";
require_once "../app/auth.php";
require_once "../app/functions.php";

if (!isAdmin()) { 
    header("Location: ../index.php"); 
    exit; 
}

// Crear categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = trim($_POST['nombre']);
    if ($nombre !== '') {
        $pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)")->execute([$nombre]);
        header("Location: categorias.php");
        exit;
    }
}

// Eliminar categoría
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([$id]);
    header("Location: categorias.php");
    exit;
}

$cats = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Admin - Categorías</title>
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
            <h1>Gestión de Categorías</h1>
        </div>

        <!-- Formulario agregar categoría -->
        <div class="admin-form">
          <h3>📂 Crear nueva categoría</h3>
          <form method="post">
            <div class="form-row">
              <label>Nombre de la categoría</label>
              <input type="text" name="nombre" placeholder="Ej: Mascotas, Seguridad, Eventos..." required>
            </div>
            <button type="submit" class="btn-primary">Agregar categoría</button>
          </form>
        </div>


        <!-- Tabla -->
        <table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th style="width: 150px;">Acciones</th>
            </tr>

            <?php foreach ($cats as $c): ?>
            <tr>
                <td><?= e($c['id']) ?></td>
                <td><?= e($c['nombre']) ?></td>
                <td>
                    <a href="?delete=<?= e($c['id']) ?>" 
                       class="btn-small btn-red"
                       onclick="return confirm('¿Eliminar esta categoría?')">
                        Eliminar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

    </main>
</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>
