<?php
session_start();
require_once 'auth_functions.php';
require_once 'database.php';

if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? 'user') !== "admin") {
    header("Location: index.php");
    exit();
}

// --- Filtros y orden ---
$filter_type = $_GET['filter_type'] ?? 'event';
$filter_id = $_GET['filter_id'] ?? '';
$order = $_GET['order'] ?? 'desc'; // 'desc' por defecto

$order_sql = ($order === 'asc') ? 'ASC' : 'DESC';

$pdo = getDBConnection();

// Top eventos
$top_events = $pdo->query("
    SELECT e.id, e.name, SUM(t.quantity) AS total_sold
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.status = 'active'
    GROUP BY e.id, e.name
    ORDER BY total_sold $order_sql
    LIMIT 5
")->fetchAll();

// Top usuarios
$top_users = $pdo->query("
    SELECT u.id, u.name, u.email, SUM(t.quantity) AS total_bought
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.status = 'active'
    GROUP BY u.id, u.name, u.email
    ORDER BY total_bought $order_sql
    LIMIT 5
")->fetchAll();

// Top organizadores
$top_orgs = $pdo->query("
    SELECT o.id, o.name, SUM(t.quantity) AS total_sold
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    JOIN org o ON e.org_id = o.id
    WHERE t.status = 'active'
    GROUP BY o.id, o.name
    ORDER BY total_sold $order_sql
    LIMIT 5
")->fetchAll();

$events = $pdo->query("SELECT id, name FROM events ORDER BY name")->fetchAll();
$users = $pdo->query("SELECT id, name FROM users ORDER BY name")->fetchAll();
$orgs = $pdo->query("SELECT id, name FROM org ORDER BY name")->fetchAll();

// --- Métricas filtradas ---
$filtered_stats = [];
$filter_title = '';
if ($filter_type === 'event' && $filter_id) {
    $stmt = $pdo->prepare("
        SELECT u.name AS user_name, t.quantity, t.purchase_date
        FROM tickets t
        JOIN users u ON t.user_id = u.id
        WHERE t.event_id = ? AND t.status = 'active'
        ORDER BY t.purchase_date DESC
    ");
    $stmt->execute([$filter_id]);
    $filtered_stats = $stmt->fetchAll();
    $event_name = '';
    foreach ($events as $ev) {
        if ($ev['id'] == $filter_id) $event_name = $ev['name'];
    }
    $filter_title = "Compradores del evento: <b>$event_name</b>";
} elseif ($filter_type === 'user' && $filter_id) {
    $stmt = $pdo->prepare("
        SELECT e.name AS event_name, t.quantity, t.purchase_date
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        WHERE t.user_id = ? AND t.status = 'active'
        ORDER BY t.purchase_date DESC
    ");
    $stmt->execute([$filter_id]);
    $filtered_stats = $stmt->fetchAll();
    $user_name = '';
    foreach ($users as $u) {
        if ($u['id'] == $filter_id) $user_name = $u['name'];
    }
    $filter_title = "Compras de: <b>$user_name</b>";
} elseif ($filter_type === 'org' && $filter_id) {
    $stmt = $pdo->prepare("
        SELECT e.name AS event_name, SUM(t.quantity) AS total_sold
        FROM tickets t
        JOIN events e ON t.event_id = e.id
        WHERE e.org_id = ? AND t.status = 'active'
        GROUP BY e.name
        ORDER BY total_sold $order_sql
    ");
    $stmt->execute([$filter_id]);
    $filtered_stats = $stmt->fetchAll();
    $org_name = '';
    foreach ($orgs as $o) {
        if ($o['id'] == $filter_id) $org_name = $o['name'];
    }
    $filter_title = "Ventas por evento del organizador: <b>$org_name</b>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - StarBillet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="icon" type="image/png" href="img/logoblanco.png">
    <style>
        
        .stats-section {
            max-width: 1200px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: #f8f8f8;
            border-radius: 0.75rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            padding: 1.5rem 1rem;
            text-align: center;
        }
        .stats-card h3 {
            font-family: "Moderniz";
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #000;
        }
        .stats-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .stats-card li {
            font-size: 1rem;
            margin-bottom: 0.7rem;
            color: #363636;
        }
        .filter-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            align-items: center;
        }
        .filter-bar select, .filter-bar button {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #ccc;
            font-size: 1rem;
            font-family: "Inter", sans-serif;
        }
        .filter-bar button {
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-family: "Moderniz";
        }
        .filter-bar button:hover {
            background: #363636;
        }
        .filtered-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        .filtered-table th, .filtered-table td {
            border: 1px solid #e0e0e0;
            padding: 0.7rem 1rem;
            text-align: left;
        }
        .filtered-table th {
            background: #f3f4f6;
            font-family: "Moderniz";
        }
        .filtered-table tr:nth-child(even) {
            background: #fafafa;
        }
        .filtered-table tr:hover {
            background: #f0f0f0;
        }
        .filter-title {
            font-family: "Moderniz";
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: #000;
        }
        @media (max-width: 700px) {
            .stats-section { padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .filter-bar { flex-direction: column; align-items: stretch; }
        }            

        .order-bar {
            margin-bottom: 1.5rem;
            text-align: right;
        }
        .order-bar a {
            background: #eee;
            color: #222;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            margin-left: 0.5rem;
            text-decoration: none;
            font-size: 1rem;
            font-family: "Inter", sans-serif;
            border: 1px solid #ccc;
        }
        .order-bar a.selected, .order-bar a:hover {
            background: #000;
            color: #fff;
            border-color: #000;
        }
    </style>
</head>
<body>
    <!-- ...header igual que antes... -->
    <main>
        <section class="stats-section">
            <h1 style="font-family:'Moderniz'; font-size:2rem; margin-bottom:2rem; color:#000;">Estadísticas y Métricas</h1>
            <div class="order-bar">
                Ordenar:
                <a href="?order=desc<?= $filter_type ? '&filter_type='.$filter_type : '' ?><?= $filter_id ? '&filter_id='.$filter_id : '' ?>" class="<?= $order==='desc'?'selected':'' ?>">Mayores ventas</a>
                <a href="?order=asc<?= $filter_type ? '&filter_type='.$filter_type : '' ?><?= $filter_id ? '&filter_id='.$filter_id : '' ?>" class="<?= $order==='asc'?'selected':'' ?>">Menores ventas</a>
            </div>
            <div class="stats-grid">
                <div class="stats-card">
                    <h3>Eventos <?= $order==='asc'?'menos':'más' ?> vendidos</h3>
                    <ul>
                        <?php foreach ($top_events as $ev): ?>
                            <li>
                                <?= htmlspecialchars($ev['name']) ?> <b>(<?= $ev['total_sold'] ?> boletos)</b>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="stats-card">
                    <h3>Usuarios que <?= $order==='asc'?'menos':'más' ?> han comprado</h3>
                    <ul>
                        <?php foreach ($top_users as $u): ?>
                            <li>
                                <?= htmlspecialchars($u['name']) ?> <b>(<?= $u['total_bought'] ?> boletos)</b>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="stats-card">
                    <h3>Organizadores con <?= $order==='asc'?'menos':'más' ?> ventas</h3>
                    <ul>
                        <?php foreach ($top_orgs as $o): ?>
                            <li>
                                <?= htmlspecialchars($o['name']) ?> <b>(<?= $o['total_sold'] ?> boletos)</b>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <!-- ...filtros y tabla igual que antes... -->
            <form method="get" class="filter-bar">
                <label for="filter_type"><b>Filtrar por:</b></label>
                <select name="filter_type" id="filter_type" onchange="this.form.submit()">
                    <option value="event" <?= $filter_type === 'event' ? 'selected' : '' ?>>Evento</option>
                    <option value="user" <?= $filter_type === 'user' ? 'selected' : '' ?>>Usuario</option>
                    <option value="org" <?= $filter_type === 'org' ? 'selected' : '' ?>>Organizador</option>
                </select>
                <?php if ($filter_type === 'event'): ?>
                    <select name="filter_id" onchange="this.form.submit()">
                        <option value="">Selecciona un evento</option>
                        <?php foreach ($events as $ev): ?>
                            <option value="<?= $ev['id'] ?>" <?= $filter_id == $ev['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ev['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($filter_type === 'user'): ?>
                    <select name="filter_id" onchange="this.form.submit()">
                        <option value="">Selecciona un usuario</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $filter_id == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($filter_type === 'org'): ?>
                    <select name="filter_id" onchange="this.form.submit()">
                        <option value="">Selecciona un organizador</option>
                        <?php foreach ($orgs as $o): ?>
                            <option value="<?= $o['id'] ?>" <?= $filter_id == $o['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <input type="hidden" name="order" value="<?= htmlspecialchars($order) ?>">
                <button type="submit">Filtrar</button>
            </form>
            <?php if ($filter_id && $filtered_stats): ?>
                <div class="filter-title"><?= $filter_title ?></div>
                <table class="filtered-table">
                    <thead>
                        <tr>
                            <?php if ($filter_type === 'event'): ?>
                                <th>Usuario</th>
                                <th>Cantidad</th>
                                <th>Fecha de compra</th>
                            <?php elseif ($filter_type === 'user'): ?>
                                <th>Evento</th>
                                <th>Cantidad</th>
                                <th>Fecha de compra</th>
                            <?php elseif ($filter_type === 'org'): ?>
                                <th>Evento</th>
                                <th>Total vendido</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_stats as $row): ?>
                            <tr>
                                <?php if ($filter_type === 'event'): ?>
                                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['purchase_date'])) ?></td>
                                <?php elseif ($filter_type === 'user'): ?>
                                    <td><?= htmlspecialchars($row['event_name']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['purchase_date'])) ?></td>
                                <?php elseif ($filter_type === 'org'): ?>
                                    <td><?= htmlspecialchars($row['event_name']) ?></td>
                                    <td><?= $row['total_sold'] ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($filter_id): ?>
                <div style="margin-top:1rem; color:#b00;">No hay datos para este filtro.</div>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        &copy; 2025 StarBillet. Todos los derechos reservados.
    </footer>
</body>
</html>