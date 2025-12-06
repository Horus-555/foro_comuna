<?php
session_start();
require_once "./app/db.php";
require_once "./app/functions.php";
require_once "./app/auth.php";

// Obtener categorías fijas desde DB
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll();

// Sidebar: publicaciones más interactivas (comentarios + vistas)
$top_sql = "
  SELECT p.id, p.titulo, p.vistas,
         COALESCE(c.cnt, 0) AS comentarios,
         (p.vistas + COALESCE(c.cnt, 0)) AS score
  FROM posts p
  LEFT JOIN (
      SELECT post_id, COUNT(*) AS cnt FROM comentarios GROUP BY post_id
  ) c ON p.id = c.post_id
  ORDER BY score DESC
  LIMIT 6
";
$top_posts = $pdo->query($top_sql)->fetchAll();

// Eventos con fecha (evento_fecha NOT NULL)
$eventos = $pdo->query("SELECT id, titulo, evento_fecha FROM posts WHERE evento_fecha IS NOT NULL")->fetchAll();

// Reorganizar eventos por fecha
$eventos_por_fecha = [];
foreach ($eventos as $ev) {
    if (!empty($ev['evento_fecha'])) {
        $eventos_por_fecha[$ev['evento_fecha']][] = $ev;
    }
}

// Mes y año del calendario
$month = isset($_GET['m']) ? intval($_GET['m']) : intval(date('m'));
$year  = isset($_GET['y']) ? intval($_GET['y']) : intval(date('Y'));

// Funciones auxiliares
function dias_en_mes($m, $y) {
    return cal_days_in_month(CAL_GREGORIAN, $m, $y);
}

function primer_dia_semana($m, $y) {
    return date('N', strtotime("$y-$m-01")); // 1 = lunes
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Foro Comunal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/css/style.css">

    <style>
        /* === Layout === */
        .layout { max-width: 1100px; margin: 0 auto; display: flex; gap: 24px; }
        .main { flex: 1 1 65%; }
        .sidebar { width: 320px; flex: 0 0 320px; }

        .category-title { background: #f1f5f8; padding: 8px 12px; border-radius: 6px; margin: 18px 0 12px; font-weight: 600; }

        .cards-row { display: flex; flex-wrap: wrap; gap: 12px; }
        .card { width: calc(25% - 9px); background: #fff; padding: 12px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .card { width: 48%; }
            .sidebar { width: auto; }
        }
        @media (max-width: 480px) {
            .card { width: 100%; }
        }

        /* === Calendario === */
        .cal { background: #fff; padding: 12px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
        .cal table { width:100%; border-collapse:collapse; }
        .cal th { text-align:center; padding:6px; font-weight:600; }
        .cal td { height:70px; vertical-align:top; padding:6px; border:1px solid #f0f0f0; position:relative; }
        .cal .daynum { position:absolute; top:6px; right:6px; font-size:12px; color:#666; }
        .evdot { width:8px; height:8px; background:#ff6b6b; border-radius:50%; display:inline-block; margin-right:4px; }
    </style>
</head>
<body>

<?php include "./includes/header.php"; ?>

<div class="layout" style="padding:18px;">
    <main class="main">

        <?php flash_show(); ?>

        <!-- Mostrar cada categoría con hasta 4 posts -->
        <?php foreach ($categorias as $cat): ?>
            <section>

                <div class="category-title"><?= e($cat['nombre']) ?></div>

                <div class="cards-row">
                    <?php
                        $stmt = $pdo->prepare("SELECT * FROM posts WHERE categoria_id = ? ORDER BY creado_en DESC LIMIT 4");
                        $stmt->execute([$cat['id']]);
                        $posts = $stmt->fetchAll();
                    ?>

                    <?php if (empty($posts)): ?>
                        <p>No hay publicaciones en esta categoría.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <article class="card">

                                <?php if (!empty($p['imagen'])): ?>
                                    <div style="height:90px; overflow:hidden; margin-bottom:8px;">
                                        <img src="./uploads/<?= e($p['imagen']) ?>" alt="<?= e($p['titulo']) ?>" style="width:100%; object-fit:cover;">
                                    </div>
                                <?php endif; ?>

                                <h4>
                                    <a href="./post_view.php?id=<?= e($p['id']) ?>">
                                        <?= e(mb_strimwidth($p['titulo'], 0, 60, "...")) ?>
                                    </a>
                                </h4>

                                <span style="font-size:12px; color:#666;">
                                    <?= e(substr($p['creado_en'], 0, 16)) ?> · <?= e($p['vistas']) ?> vistas
                                </span>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div style="margin-top:10px;">
                    <a href="./categoria.php?id=<?= e($cat['id']) ?>">Ver más en <?= e($cat['nombre']) ?></a>
                </div>
            </section>
        <?php endforeach; ?>

    </main>

    <aside class="sidebar">

        <!-- === Calendario === -->
        <div class="cal">
            <?php
            // Controladores de mes
            $prev_m = $month - 1; $prev_y = $year;
            if ($prev_m < 1) { $prev_m = 12; $prev_y--; }

            $next_m = $month + 1; $next_y = $year;
            if ($next_m > 12) { $next_m = 1; $next_y++; }
            ?>

            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <a href="./index.php?m=<?= $prev_m ?>&y=<?= $prev_y ?>">&lt;</a>
                <strong><?= strftime("%B", strtotime("$year-$month-01")) . " $year" ?></strong>
                <a href="./index.php?m=<?= $next_m ?>&y=<?= $next_y ?>">&gt;</a>
            </div>

            <?php
            $days = dias_en_mes($month, $year);
            $start_weekday = primer_dia_semana($month, $year);
            $cells = $start_weekday - 1 + $days;
            $weeks = ceil($cells / 7);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th>
                        <th>Vie</th><th>Sáb</th><th>Dom</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $day = 1;
                for ($w = 0; $w < $weeks; $w++): ?>
                    <tr>
                    <?php for ($d = 1; $d <= 7; $d++):
                        $cellIndex = $w * 7 + $d;
                        $cellday = $cellIndex - ($start_weekday - 1);

                        if ($cellday < 1 || $cellday > $days) {
                            echo "<td></td>";
                            continue;
                        }

                        $fecha = sprintf("%04d-%02d-%02d", $year, $month, $cellday);
                        $hasEvents = isset($eventos_por_fecha[$fecha]);
                        ?>
                        <td>
                            <div class="daynum"><?= $cellday ?></div>

                            <?php if ($hasEvents): ?>
                                <?php foreach ($eventos_por_fecha[$fecha] as $ev): ?>
                                    <div style="margin-top:18px;">
                                        <span class="evdot"></span>
                                        <a href="./post_view.php?id=<?= e($ev['id']) ?>">
                                            <?= e(mb_strimwidth($ev['titulo'], 0, 22, "...")) ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- === Más interactivas === -->
        <div class="cal" style="margin-top:18px;">
            <h4>Publicaciones más interactivas</h4>

            <?php foreach ($top_posts as $tp): ?>
                <div style="padding:8px 0; border-bottom:1px dashed #ddd;">
                    <a href="./post_view.php?id=<?= e($tp['id']) ?>">
                        <?= e(mb_strimwidth($tp['titulo'], 0, 60, "...")) ?>
                    </a>
                    <div style="font-size:12px; color:#666;">
                        <?= e($tp['comentarios']) ?> comentarios · <?= e($tp['vistas']) ?> vistas
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </aside>
</div>

<?php include "./includes/footer.php"; ?>
</body>
</html>
