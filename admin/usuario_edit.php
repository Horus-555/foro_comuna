<?php
session_start();
require_once "../app/db.php";
require_once "../app/auth.php";
require_once "../app/functions.php";

if (!isAdmin()) {
    header("Location: ../index.php");
    exit;
}

// ---------------------------------------------
// Validar ID
// ---------------------------------------------
if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = intval($_GET['id']);

// ---------------------------------------------
// Evitar que un admin se elimine a sí mismo
// ---------------------------------------------
$yo = $_SESSION['usuario_id'];

// ---------------------------------------------
// PROCESAR ELIMINACIÓN (solo usuario)
// ---------------------------------------------
if (isset($_POST['delete_user'])) {

    if ($id == $yo) {
        header("Location: usuario_edit.php?id=$id&error=self_delete");
        exit;
    }

    $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);

    header("Location: usuarios.php?deleted=1");
    exit;
}

// ---------------------------------------------
// PROCESAR ELIMINACIÓN (usuario + posts)
// ---------------------------------------------
if (isset($_POST['delete_user_posts'])) {

    if ($id == $yo) {
        header("Location: usuario_edit.php?id=$id&error=self_delete");
        exit;
    }

    $pdo->prepare("DELETE FROM posts WHERE usuario_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);

    header("Location: usuarios.php?deleted_all=1");
    exit;
}

// ---------------------------------------------
// Obtener datos del usuario
// ---------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: usuarios.php");
    exit;
}

// ---------------------------------------------
// GUARDAR CAMBIOS
// ---------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $rut = trim($_POST['rut']);
    $role = $_POST['role'];

    $update = $pdo->prepare("
        UPDATE usuarios 
        SET nombre = ?, apellido = ?, email = ?, rut = ?, role = ? 
        WHERE id = ?
    ");
    $update->execute([$nombre, $apellido, $email, $rut, $role, $id]);

    header("Location: usuarios.php?saved=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar usuario</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../admin/assets/css/style.css">

<style>
.delete-box {
    margin-top: 25px;
    padding: 20px;
    border-radius: 10px;
    background: #fff4f4;
    border: 1px solid #f5b5b5;
}
</style>

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

    <main class="admin-content">

        <div class="admin-card">
            <h1 class="admin-title">Editar usuario</h1>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'self_delete'): ?>
                <div class="alert alert-danger">
                    ❌ No puedes eliminar tu propia cuenta.
                </div>
            <?php endif; ?>

            <form method="post" class="admin-form">

                <div class="form-row">
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?= e($user['nombre']) ?>" required>
                </div>

                <div class="form-row">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?= e($user['apellido']) ?>" required>
                </div>

                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= e($user['email']) ?>">
                </div>

                <div class="form-row">
                    <label>RUT</label>
                    <input type="text" name="rut" value="<?= e($user['rut']) ?>" required>
                </div>

                <div class="form-row">
                    <label>Rol</label>
                    <select name="role" required>
                        <option value="usuario" <?= $user['role'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mt-3">💾 Guardar Cambios</button>
                <a href="usuarios.php" class="btn btn-secondary mt-3">Cancelar</a>
            </form>

            <!-- Zona de eliminación -->
            <div class="delete-box">
                <h4 class="text-danger">⚠ Opciones de eliminación</h4>
                <p>Puedes eliminar solo el usuario o eliminar el usuario junto con todas sus publicaciones.</p>

                <!-- Botón 1 -->
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                    🗑 Eliminar SOLO Usuario
                </button>

                <!-- Botón 2 -->
                <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#deleteUserPostsModal">
                    🔥 Eliminar Usuario + TODOS sus Posts
                </button>
            </div>

        </div>

    </main>

</div>


<!-- MODAL 1: Eliminar solo usuario -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Eliminar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p>¿Seguro que deseas eliminar SOLO el usuario "<strong><?= e($user['nombre']." ".$user['apellido']) ?></strong>"?</p>
        <p class="text-danger fw-bold">Esta acción no se puede deshacer.</p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" name="delete_user" class="btn btn-danger">Eliminar Usuario</button>
      </div>
    </form>
  </div>
</div>


<!-- MODAL 2: Eliminar usuario + posts -->
<div class="modal fade" id="deleteUserPostsModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Eliminar Usuario + Posts</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p>¿Seguro que deseas eliminar completamente al usuario 
            "<strong><?= e($user['nombre']." ".$user['apellido']) ?></strong>" 
            y <strong>TODAS sus publicaciones</strong>?</p>

        <p class="text-danger fw-bold">Esta acción es irreversible.</p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" name="delete_user_posts" class="btn btn-dark">
            🔥 Eliminar Usuario + Posts
        </button>
      </div>
    </form>
  </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
