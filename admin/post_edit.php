<?php
session_start();
require_once "../app/db.php";
require_once "../app/auth.php";
require_once "../app/functions.php";

if (!isAdmin()) {
    header("Location: ../index.php");
    exit;
}

// -------------------------------------------------------
// VALIDAR ID
// -------------------------------------------------------
if (!isset($_GET['id'])) {
    header("Location: post.php");
    exit;
}

$id = intval($_GET['id']);

// -------------------------------------------------------
// OBTENER POST
// -------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: post.php");
    exit;
}

// -------------------------------------------------------
// OBTENER CATEGORÍAS
// -------------------------------------------------------
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll();

// -------------------------------------------------------
// GUARDAR CAMBIOS
// -------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = intval($_POST['categoria_id']);

    // Imagen actual
    $imagenActual = $post['imagen'];

    // -------------------------------------------------------
    // PROCESAR IMAGEN (si se envía)
    // -------------------------------------------------------
    if (!empty($_FILES['imagen']['name'])) {

        $fileTmp = $_FILES['imagen']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['imagen']['name']);
        $destino = "../uploads/" . $fileName;

        // Crear carpeta si no existe
        if (!is_dir("../uploads")) {
            mkdir("../uploads", 0777, true);
        }

        move_uploaded_file($fileTmp, $destino);

        // Eliminar la imagen anterior (si existe)
        if ($imagenActual && file_exists("../uploads/" . $imagenActual)) {
            unlink("../uploads/" . $imagenActual);
        }

        $imagenNueva = $fileName;

    } else {
        $imagenNueva = $imagenActual;
    }

    // -------------------------------------------------------
    // ACTUALIZAR POST
    // -------------------------------------------------------
    $update = $pdo->prepare("
        UPDATE posts 
        SET titulo = ?, contenido = ?, categoria_id = ?, imagen = ?
        WHERE id = ?
    ");

    $update->execute([
        $titulo,
        $contenido,
        $categoria_id,
        $imagenNueva,
        $id
    ]);

    header("Location: post.php?updated=1");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar Publicación</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../admin/assets/css/style.css">

<style>
.edit-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 3px 10px #00000025;
}

.preview-img {
    width: 100%;
    max-width: 250px;
    border-radius: 10px;
    margin-top: 10px;
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

    <!-- Contenido -->
    <main class="admin-content">

        <div class="edit-card">
            <h1 class="mb-3">Editar Publicación</h1>

            <form method="post" enctype="multipart/form-data">

                <!-- TÍTULO -->
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required
                           value="<?= e($post['titulo']) ?>">
                </div>

                <!-- CONTENIDO -->
                <div class="mb-3">
                    <label class="form-label">Contenido</label>
                    <textarea name="contenido" rows="6" class="form-control" required><?= e($post['contenido']) ?></textarea>
                </div>

                <!-- CATEGORÍA -->
                <div class="mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= $post['categoria_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- IMAGEN -->
                <div class="mb-3">
                    <label class="form-label">Imagen</label>
                    <input type="file" name="imagen" class="form-control">

                    <?php if ($post['imagen']): ?>
                        <p class="mt-2">Imagen actual:</p>
                        <img src="../uploads/<?= e($post['imagen']) ?>" class="preview-img">
                    <?php endif; ?>
                </div>

                <!-- BOTONES -->
                <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
                <a href="post.php" class="btn btn-secondary">Cancelar</a>

            </form>

        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
