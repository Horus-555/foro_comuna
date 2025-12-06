<?php
require_once __DIR__ . '/../app/auth.php';
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/functions.php';

// Obtener categorías
$cat = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll();
?>

<header class="main-header">
    <div class="header-left">
        <a href="./index.php" class="logo">Comunidad</a>
    </div>

    <nav class="header-center">
        <ul>
            <li><a href="./index.php">Inicio</a></li>

            <li class="dropdown">
                <a href="#">Categorías ▾</a>
                <ul class="dropdown-menu">
                    <?php foreach ($cat as $c): ?>
                        <li><a href="./categoria.php?id=<?= e($c['id']) ?>"><?= e($c['nombre']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <li><a href="./calendario.php">Calendario</a></li>
            <li><a href="./post_create.php">Crear Publicación</a></li>
        </ul>
    </nav>

    <div class="header-right">
        <?php if (isLoggedIn()): ?>
            <span class="user-name">
                <?= e($_SESSION['usuario_nombre'] . ' ' . $_SESSION['usuario_apellido']) ?>
            </span>

            <?php if (isAdmin()): ?>
                <a href="./admin/index.php" class="admin-btn">Admin</a>
            <?php endif; ?>

            <a href="./logout.php" class="logout-btn">Cerrar sesión</a>

        <?php else: ?>
            <a href="./login.php" class="login-btn">Iniciar sesión</a>
        <?php endif; ?>
    </div>
</header>
