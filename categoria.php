<?php
session_start();
require_once "app/db.php";
require_once "app/functions.php";
require_once "app/auth.php";

if (!isset($_GET['id'])) {
    die("Categoría no válida");
}

$cat_id = intval($_GET['id']);

// Obtener nombre de la categoría
$stmt = $pdo->prepare("SELECT nombre FROM categorias WHERE id = ? LIMIT 1");
$stmt->execute([$cat_id]);
$categoria = $stmt->fetch();

if (!$categoria) {
    die("La categoría no existe.");
}

// Obtener posts de esa categoría
$posts = $pdo->prepare("
    SELECT p.*, u.nombre AS autor_nombre, u.apellido AS autor_apellido
    FROM posts p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.categoria_id = ?
    ORDER BY p.creado_en DESC
");
$posts->execute([$cat_id]);
$lista = $posts->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Categoría: <?= e($categoria['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include __DIR__ . "/includes/header.php"; ?>

<div class="container my-4">
    <h2 class="mb-4">Publicaciones en: <strong><?= e($categoria['nombre']) ?></strong></h2>

    <?php if (count($lista) === 0): ?>
        <p>No hay publicaciones en esta categoría.</p>

    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($lista as $p): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm post-card-grid">

                        <?php if (!empty($p['imagen'])): ?>
                            <a href="./post_view.php?id=<?= $p['id'] ?>">
                                <img src="./uploads/<?= e($p['imagen']) ?>" class="card-img-top" alt="">
                            </a>
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="./post_view.php?id=<?= $p['id'] ?>" class="text-decoration-none">
                                    <?= e($p['titulo']) ?>
                                </a>
                            </h5>

                            <p class="card-text text-muted small mb-2">
                                Publicado por <strong><?= e($p['autor_nombre'] . " " . $p['autor_apellido']) ?></strong> <br>
                                <?= e($p['creado_en']) ?> · <?= e($p['vistas']) ?> vistas
                            </p>

                            <p class="card-text">
                                <?= nl2br(e(substr($p['contenido'], 0, 120))) ?>...
                            </p>
                        </div>

                        <div class="card-footer bg-white border-0">
                            <a href="./post_view.php?id=<?= $p['id'] ?>" class="btn btn-primary w-100">
                                Leer más
                            </a>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<?php include __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
