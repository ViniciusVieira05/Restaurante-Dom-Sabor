<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';

if ($_SESSION['perfil'] != 'admin') {
    die('Acesso negado.');
}

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$garcom_id = $_GET['garcom_id'] ?? '';

$garcons = $pdo->query("
    SELECT
        f.id,
        f.nome
    FROM funcionarios f
    INNER JOIN usuarios u
        ON u.id = f.usuario_id
    WHERE u.perfil = 'garcom'
    AND f.status = 'ativo'
    ORDER BY f.nome
    ")->fetchAll();
$where = ["p.status = 'finalizado'"];
$params = [];

if (!empty($data_inicio)) {
    $where[] = 'DATE(p.data_pedido) >= ?';
    $params[] = $data_inicio;
}

if (!empty($data_fim)) {
    $where[] = 'DATE(p.data_pedido) <= ?';
    $params[] = $data_fim;
}

if (!empty($garcom_id)) {
    $where[] = 'p.garcom_id = ?';
    $params[] = $garcom_id;
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$total_vendas = $pdo->prepare(
    "SELECT SUM(ip.quantidade * ip.preco_unitario) as total
     FROM pedidos p
     INNER JOIN itens_pedido ip ON ip.pedido_id = p.id
     $where_sql"
);

$total_vendas->execute($params);
$total_vendas = $total_vendas->fetch()['total'] ?? 0;

$pedidos = $pdo->prepare(
    "SELECT 
         p.id,
         c.nome as cliente,
        f.nome as garcom,
         p.data_pedido,
         SUM(ip.quantidade * ip.preco_unitario) as total
     FROM pedidos p
     LEFT JOIN clientes c ON c.id = p.cliente_id
     LEFT JOIN funcionarios f
    ON f.id = p.garcom_id
     INNER JOIN itens_pedido ip ON ip.pedido_id = p.id
     $where_sql
     GROUP BY p.id
     ORDER BY p.data_pedido DESC"
);

$pedidos->execute($params);
$pedidos = $pedidos->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Vendas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container my-5">
    <h1>Relatório de Vendas</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($data_inicio); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($data_fim); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Garçom</label>
                    <select name="garcom_id" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($garcons as $g): ?>
                            <option value="<?php echo $g['id']; ?>" <?php echo $garcom_id == $g['id'] ? 'selected' : ''; ?>><?php echo $g['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Total do Relatório</h5>
                    <p class="mb-0">R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Critérios</h5>
                    <p class="mb-0"><?php echo $data_inicio ? 'De ' . date('d/m/Y', strtotime($data_inicio)) : 'De início'; ?> até <?php echo $data_fim ? date('d/m/Y', strtotime($data_fim)) : 'hoje'; ?></p>
                    <p><?php echo $garcom_id ? 'Garçom: ' . ($garcons[array_search($garcom_id, array_column($garcons, 'id'))]['nome'] ?? '') : 'Todos os garçons'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <h4 class="mb-4">Pedidos Finalizados</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Garçom</th>
                            <th>Data</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pedidos)): ?>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?php echo $pedido['id']; ?></td>
                                    <td><?php echo $pedido['cliente']; ?></td>
                                    <td><?php echo $pedido['garcom']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                                    <td>R$ <?php echo number_format($pedido['total'] ?? 0, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">Nenhum pedido encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
