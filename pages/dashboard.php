<?php

require_once '../includes/config.php';
require_once '../php/Pedido.php';
require_once '../includes/auth.php';


$pedido = new Pedido();

$total_pedidos = $pdo->query(
    "SELECT COUNT(*) as total FROM pedidos"
)->fetch()['total'];


$pedidos_abertos = $pdo->query(
    "SELECT COUNT(*) as total
     FROM pedidos
     WHERE status = 'aberto'"
)->fetch()['total'];


$pedidos_finalizados = $pdo->query(
    "SELECT COUNT(*) as total
     FROM pedidos
     WHERE status = 'finalizado'"
)->fetch()['total'];


$total_clientes = $pdo->query(
    "SELECT COUNT(*) as total
     FROM clientes"
)->fetch()['total'];


$total_produtos = $pdo->query(
    "SELECT COUNT(*) as total
     FROM produtos"
)->fetch()['total'];


$faturamento = $pdo->query(
    "SELECT SUM(ip.quantidade * ip.preco_unitario)
     as total

     FROM itens_pedido ip

     INNER JOIN pedidos p
     ON p.id = ip.pedido_id

     WHERE p.status = 'finalizado'"
)->fetch()['total'];

$faturamento = $faturamento ?? 0;

$total_despesas = $pdo->query(
    "SELECT SUM(valor) as total FROM despesas"
)->fetch()['total'];
$total_despesas = $total_despesas ?? 0;

$faturamento_liquido = $faturamento - $total_despesas;

$mesas_ocupadas = $pdo->query(
    "SELECT COUNT(*) as total FROM mesas WHERE status = 'ocupada'"
)->fetch()['total'];

$pedidos_cancelados = $pdo->query(
    "SELECT COUNT(*) as total FROM pedidos WHERE status = 'cancelado'"
)->fetch()['total'];

$faturamento_hoje = $pdo->query(
    "SELECT SUM(ip.quantidade * ip.preco_unitario) as total
     FROM itens_pedido ip
     INNER JOIN pedidos p ON p.id = ip.pedido_id
     WHERE p.status = 'finalizado' AND DATE(p.data_pedido) = CURDATE()"
)->fetch()['total'];

$faturamento_hoje = $faturamento_hoje ?? 0;

$top_garcons = $pdo->query(
    "SELECT u.nome as garcom, SUM(ip.quantidade * ip.preco_unitario) as total
     FROM pedidos p
     LEFT JOIN usuarios u ON u.id = p.garcom_id
     LEFT JOIN itens_pedido ip ON ip.pedido_id = p.id
     WHERE p.status = 'finalizado'
     GROUP BY p.garcom_id
     ORDER BY total DESC
     LIMIT 3"
)->fetchAll();

$ultimos_pedidos = $pdo->query(

    "SELECT
        p.id,
        c.nome as cliente,
        p.status,
        p.data_pedido,
        SUM(ip.quantidade * ip.preco_unitario) as total

     FROM pedidos p

     LEFT JOIN clientes c
     ON c.id = p.cliente_id

     LEFT JOIN itens_pedido ip
     ON ip.pedido_id = p.id

     GROUP BY p.id
     ORDER BY p.data_pedido DESC
     LIMIT 5"

)->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <title>Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link href="../css/style.css" rel="stylesheet">

</head>

<body>

<?php include '../includes/header.php'; ?>

<div class="container my-5">

    <div class="dashboard-header mb-5">
        <h1>Dashboard do Restaurante</h1>
        <p>
            Visão geral das vendas, pedidos, clientes e desempenho do negócio.
        </p>
    </div>

    <div class="row g-4">

        <!-- TOTAL PEDIDOS -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">

                    <h5>Total de Pedidos</h5>

                    <h2>
                        <?php echo $total_pedidos; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- PEDIDOS ABERTOS -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">

                    <h5>Pedidos Abertos</h5>

                    <h2>
                        <?php echo $pedidos_abertos; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- FINALIZADOS -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">

                    <h5>Pedidos Finalizados</h5>

                    <h2>
                        <?php echo $pedidos_finalizados; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- CLIENTES -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">

                    <h5>Clientes</h5>

                    <h2>
                        <?php echo $total_clientes; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- PRODUTOS -->
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">

                    <h5>Produtos</h5>

                    <h2>
                        <?php echo $total_produtos; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- FATURAMENTO -->
        <div class="col-md-4">
            <div class="card shadow bg-success text-white">
                <div class="card-body">

                    <h5>Faturamento Bruto</h5>

                    <h2>
                        R$ <?php echo number_format($faturamento, 2, ',', '.'); ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- DESPESAS -->
        <div class="col-md-4">
            <div class="card shadow bg-danger text-white">
                <div class="card-body">

                    <h5>Total Despesas</h5>

                    <h2>
                        R$ <?php echo number_format($total_despesas, 2, ',', '.'); ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- FATURAMENTO LÍQUIDO -->
        <div class="col-md-4">
            <div class="card shadow bg-dark text-white">
                <div class="card-body">

                    <h5>Faturamento Líquido</h5>

                    <h2>
                        R$ <?php echo number_format($faturamento_liquido, 2, ',', '.'); ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- MESAS OCUPADAS -->
        <div class="col-md-4">
            <div class="card shadow bg-warning text-dark">
                <div class="card-body">

                    <h5>Mesas Ocupadas</h5>

                    <h2>
                        <?php echo $mesas_ocupadas; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- PEDIDOS CANCELADOS -->
        <div class="col-md-4">
            <div class="card shadow bg-danger text-white">
                <div class="card-body">

                    <h5>Pedidos Cancelados</h5>

                    <h2>
                        <?php echo $pedidos_cancelados; ?>
                    </h2>

                </div>
            </div>
        </div>

        <!-- FATURAMENTO HOJE -->
        <div class="col-md-4">
            <div class="card shadow bg-info text-white">
                <div class="card-body">

                    <h5>Faturamento Hoje</h5>

                    <h2>
                        R$ <?php echo number_format($faturamento_hoje, 2, ',', '.'); ?>
                    </h2>

                </div>
            </div>
        </div>

    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Top Garçons</h5>

                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Garçom</th>
                                <th>Vendas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($top_garcons)): ?>
                                <?php foreach ($top_garcons as $g): ?>
                                    <tr>
                                        <td><?php echo $g['garcom'] ?: 'Sem garçom'; ?></td>
                                        <td>R$ <?php echo number_format($g['total'] ?? 0, 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2">Nenhum garçom com vendas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h5>Relatório de Vendas</h5>
                    <p class="mb-3">Acesse o relatório para ver pedidos finalizados por período e por garçom.</p>
                    <a href="relatorio_vendas.php" class="btn btn-primary">Abrir Relatório</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ÚLTIMOS PEDIDOS -->

    <div class="card shadow mt-5">

        <div class="card-body">

            <h4 class="mb-4">
                Últimos Pedidos
            </h4>

            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php foreach($ultimos_pedidos as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td><?php echo $p['cliente']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($p['data_pedido'])); ?></td>
                        <td>R$ <?php echo number_format($p['total'] ?? 0, 2, ',', '.'); ?></td>
                        <td>
                            <?php if($p['status'] == 'aberto'): ?>
                                <span class="badge bg-warning">Aberto</span>
                            <?php elseif($p['status'] == 'cancelado'): ?>
                                <span class="badge bg-danger">Cancelado</span>
                            <?php else: ?>
                                <span class="badge bg-success">Finalizado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>

            </table>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>