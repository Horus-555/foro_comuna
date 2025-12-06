<?php
session_start();
require_once "./app/db.php";
require_once "./app/functions.php";
require_once "./app/auth.php";

if (!isset($_GET['id'])) { die("ID inválido"); }
$id = intval($_GET['id']);

// Incrementar vistas
$pdo->prepare("UPDATE posts SET vistas = vistas + 1 WHERE id = ?")->execute([$id]);

// Obtener post con autor y categoría
$stmt = $pdo->prepare("SELECT posts.*, usuarios.nombre, usuarios.apellido, 
                       categorias.nombre AS categoria
                       FROM posts
                       JOIN usuarios ON posts.usuario_id = usuarios.id
                       JOIN categorias ON posts.categoria_id = categorias.id
                       WHERE posts.id = ? LIMIT 1");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) die("Publicación no encontrada");

// Comentarios
$comentarios = $pdo->prepare("SELECT c.*, u.nombre, u.apellido 
                              FROM comentarios c 
                              JOIN usuarios u ON c.usuario_id = u.id 
                              WHERE c.post_id = ? ORDER BY c.creado_en ASC");
$comentarios->execute([$id]);
$coms = $comentarios->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= e($post['titulo']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container" style="padding-top:8px;">
  <?php flash_show(); ?>

  <article class="post-card">
    <h2><?= e($post['titulo']) ?></h2>

    <div class="meta">
      Publicado por 
      <strong><?= e($post['nombre'].' '.$post['apellido']) ?></strong> · 
      <?= e($post['categoria']) ?> · 
      <?= e($post['creado_en']) ?> · 
      <?= e($post['vistas']) ?> vistas
    </div>

    <?php if (!empty($post['imagen'])): ?>
      <p>
        <img src="./uploads/<?= e($post['imagen']) ?>" 
             alt="<?= e($post['titulo']) ?>" 
             style="max-width:100%; border-radius:8px;">
      </p>
    <?php endif; ?>

    <?php if (!empty($post['evento_fecha'])): ?>
      <p><strong>Fecha del evento:</strong> <?= e($post['evento_fecha']) ?></p>
    <?php endif; ?>

    <div><?= nl2br(e($post['contenido'])) ?></div>
  </article>

  <section style="margin-top:18px;">
    <h3>Comentarios (<?= count($coms) ?>)</h3>

    <?php foreach ($coms as $c): ?>
      <div class="post-card" style="padding:10px;">
        <div class="meta">
          <strong><?= e($c['nombre'].' '.$c['apellido']) ?></strong> · 
          <?= e($c['creado_en']) ?>
        </div>
        <p><?= nl2br(e($c['contenido'])) ?></p>
      </div>
    <?php endforeach; ?>

    <?php if (isLoggedIn()): ?>
      <form method="post" action="./post_view.php?id=<?= $id ?>" style="margin-top:12px;">
        <label>Agregar comentario</label>
        <textarea name="comentario" required></textarea>
        <button type="submit">Comentar</button>
      </form>
    <?php else: ?>
      <p><a href="./login.php">Inicia sesión</a> para comentar.</p>
    <?php endif; ?>
  </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

<?php
// Procesar envío de comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {

    if (!isLoggedIn()) {
        flash_set('error', 'Debes iniciar sesión para comentar.');
        header("Location: ./login.php");
        exit;
    }

    $texto = trim($_POST['comentario']);
    if ($texto !== '') {
        $ins = $pdo->prepare("INSERT INTO comentarios (post_id, usuario_id, contenido) 
                              VALUES (?, ?, ?)");
        $ins->execute([$id, $_SESSION['usuario_id'], $texto]);
    }

    header("Location: ./post_view.php?id=" . $id);
    exit;
}
?>
