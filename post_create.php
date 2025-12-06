<?php
session_start();
require_once "app/db.php";
require_once "app/auth.php";
require_once "app/functions.php";

requireLogin();

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = intval($_POST['categoria']);
    $direccion = trim($_POST['direccion']);
    $evento_fecha = !empty($_POST['evento_fecha']) ? $_POST['evento_fecha'] : null;

    if ($titulo === '' || $contenido === '') {
        $msg = 'Título y contenido son obligatorios.';
    } else {
        // imagen opcional
        $imagen_nombre = null;
        if (!empty($_FILES['imagen']['name'])) {
            $f = $_FILES['imagen'];
            if ($f['error'] === 0) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $imagen_nombre = uniqid('img_') . '.' . $ext;
                $dest = __DIR__ . '/uploads/' . $imagen_nombre;
                move_uploaded_file($f['tmp_name'], $dest);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO posts (titulo, contenido, usuario_id, categoria_id, imagen, direccion, evento_fecha) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $contenido, $_SESSION['usuario_id'], $categoria_id, $imagen_nombre, $direccion, $evento_fecha]);

        flash_set('success', 'Publicación creada.');
        header("Location: ./index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear publicación</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container">
  <?php flash_show(); if ($msg): ?><div class="alert error"><?= e($msg) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data">
    <label>Título</label>
    <input type="text" name="titulo" required>

    <label>Contenido</label>
    <textarea name="contenido" required></textarea>

    <label>Categoría</label>
    <select name="categoria" required>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= e($c['id']) ?>"><?= e($c['nombre']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Dirección (opcional)</label>
    <input type="text" name="direccion">

    <label>Fecha del evento (opcional, aplica para Eventos)</label>
    <input type="date" name="evento_fecha">

    <label>Imagen (opcional)</label>
    <input type="file" name="imagen" accept="image/*">

    <button type="submit">Publicar</button>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
