<?php
require_once '../includes/config.php';
require_once '../php/Pedido.php';
require_once '../includes/auth.php';

$pedido_obj = new Pedido();

if (isset($_POST['finalizar_pedido'])) {

    $pedido_id = $_POST['pedido_id'];
    $forma_pagamento = $_POST['forma_pagamento'];

    try {

        if (isGarcom() && !pedidoPertenceAoGarcom($pdo, $pedido_id)) {
            throw new Exception('Pedido não pertence a este garçom.');
        }

        $pedido_obj->finalizarPedido($pedido_id, $forma_pagamento);

        header("Location: visualizar_pedidos.php");
        exit;

    } catch (Exception $e) {

        echo "
        <div class='alert alert-danger'>
            {$e->getMessage()}
        </div>
        ";
    }
}

if (isset($_GET['finalizar'])) {

    $pedido_id = $_GET['finalizar'];

    try {
        if (isGarcom() && !pedidoPertenceAoGarcom($pdo, $pedido_id)) {
            throw new Exception('Pedido não pertence a este garçom.');
        }

        $pedido_obj->finalizarPedido($pedido_id);

        header("Location: visualizar_pedidos.php");
        exit;

    } catch (Exception $e) {

        echo "
        <div class='alert alert-danger'>
            {$e->getMessage()}
        </div>
        ";
    }
}

if (isset($_GET['cancelar'])) {

    $pedido_id = $_GET['cancelar'];

    try {

        $pedido_obj->cancelarPedido($pedido_id);

        header("Location: visualizar_pedidos.php");
        exit;

    } catch (Exception $e) {

        echo "
        <div class='alert alert-danger'>
            {$e->getMessage()}
        </div>
        ";
    }
}

$filtro = $_GET['filtro'] ?? 'todos';
$mesa_id = $_GET['mesa_id'] ?? '';
$garcom_filter = $_GET['garcom_id'] ?? '';
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
$pedido_id = $_GET['pedido_id'] ?? null;

$mesas = $pdo->query("SELECT id, numero FROM mesas ORDER BY numero")->fetchAll();

// Obter pedido específico se informado
$pedido_detalhes = null;
$itens_detalhes = [];
$parameters = [];
$conditions = [];

if ($pedido_id) {
    $sql = "SELECT p.*, c.nome as cliente_nome, m.numero as mesa_numero, u.nome as garcom_nome FROM pedidos p 
                           LEFT JOIN clientes c ON p.cliente_id = c.id 
                           LEFT JOIN mesas m ON p.mesa_id = m.id 
                           LEFT JOIN funcionarios f
                                ON p.garcom_id = f.id

                            LEFT JOIN usuarios u
                                ON f.usuario_id = u.id
                           WHERE p.id = ?";
    $detailParameters = [$pedido_id];

    if (isGarcom()) {
        $sql .= " AND p.garcom_id = ?";
        $detailParameters[] = $_SESSION['id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($detailParameters);
    $pedido_detalhes = $stmt->fetch();

    if (!$pedido_detalhes) {
        $erro = 'Pedido não encontrado ou sem acesso.';
    } else {
        $itens_detalhes = $pedido_obj->obterItens($pedido_id);
    }
}

// Obter lista de pedidos conforme filtro
$query = "
    SELECT
        p.*,
        c.nome AS cliente_nome,
        m.numero AS mesa_numero,
        f.nome AS garcom_nome
    FROM pedidos p
    LEFT JOIN clientes c
        ON p.cliente_id = c.id
    LEFT JOIN mesas m
        ON p.mesa_id = m.id
    LEFT JOIN funcionarios f
        ON p.garcom_id = f.id
    ";

if ($filtro === 'abertos') {
    $conditions[] = "p.status = 'aberto'";
} elseif ($filtro === 'finalizados') {
    $conditions[] = "p.status = 'finalizado'";
}

if (!empty($mesa_id)) {
    $conditions[] = "p.mesa_id = ?";
    $parameters[] = $mesa_id;
}

if (!isGarcom() && !empty($garcom_filter)) {
    $conditions[] = "p.garcom_id = ?";
    $parameters[] = $garcom_filter;
}


if (isGarcom()) {

    $stmt = $pdo->prepare("
        SELECT id
        FROM funcionarios
        WHERE usuario_id = ?
    ");

    $stmt->execute([$_SESSION['id']]);

    $funcionario = $stmt->fetch();

    $conditions[] = "p.garcom_id = ?";
    $parameters[] = $funcionario['id'];
}

if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($parameters);
$pedidos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Pedidos - Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container my-5">
        <h1>Visualizar Pedidos</h1>

        <!-- Filtros -->
        <div class="mb-4">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="filtro" class="form-select">
                        <option value="todos" <?php echo $filtro === 'todos' ? 'selected' : ''; ?>>Todos</option>
                        <option value="abertos" <?php echo $filtro === 'abertos' ? 'selected' : ''; ?>>Abertos</option>
                        <option value="finalizados" <?php echo $filtro === 'finalizados' ? 'selected' : ''; ?>>Finalizados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mesa</label>
                    <select name="mesa_id" class="form-select">
                        <option value="">Todas as mesas</option>
                        <?php foreach ($mesas as $mesa): ?>
                            <option value="<?php echo $mesa['id']; ?>" <?php echo $mesa_id == $mesa['id'] ? 'selected' : ''; ?>>Mesa <?php echo $mesa['numero']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!isGarcom()): ?>
                <div class="col-md-3">
                    <label class="form-label">Garçom</label>
                    <select name="garcom_id" class="form-select">
                        <option value="">Todos os garçons</option>
                        <?php foreach ($garcons as $garcom): ?>
                            <option value="<?php echo $garcom['id']; ?>" <?php echo $garcom_filter == $garcom['id'] ? 'selected' : ''; ?>><?php echo $garcom['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Aplicar filtros</button>
                    <?php if ($filtro !== 'todos' || !empty($mesa_id) || (!isGarcom() && !empty($garcom_filter))): ?>
                        <a href="visualizar_pedidos.php" class="btn btn-link mt-2 p-0">Limpar filtros</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if ($pedido_id && $pedido_detalhes): ?>
            <!-- Exibição detalhada de um pedido -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pedido #<?php echo $pedido_detalhes['id']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Cliente:</strong> <?php echo $pedido_detalhes['cliente_nome']; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Mesa:</strong> <?php echo $pedido_detalhes['mesa_numero']; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Garçom:</strong> <?php echo $pedido_detalhes['garcom_nome'] ?? 'Não informado'; ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong>
                            <?php if ($pedido_detalhes['status'] === 'aberto'): ?>
                                <span class="badge bg-warning">Aberto</span>
                            <?php elseif ($pedido_detalhes['status'] === 'finalizado'): ?>
                                <span class="badge bg-success">Finalizado</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Cancelado</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Itens do Pedido</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_detalhes as $item): ?>
                                <tr>
                                    <td><?php echo $item['nome']; ?></td>
                                    <td><?php echo $item['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="alert alert-info">
                        <h5>Total: <strong>R$ <?php echo number_format($pedido_obj->obterTotal($pedido_id), 2, ',', '.'); ?></strong></h5>
                    </div>

                    <div class="mt-3">
                        <?php if ($pedido_detalhes['status'] === 'aberto'): ?>
                            <a href="adicionar_itens.php?pedido_id=<?php echo $pedido_id; ?>" class="btn btn-primary">Adicionar Itens</a>
                            <a href="visualizar_pedidos.php" class="btn btn-secondary">Voltar</a>
                        <?php else: ?>
                            <a href="visualizar_pedidos.php" class="btn btn-secondary">Voltar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Lista de pedidos -->
            <?php if (empty($pedidos)): ?>
                <div class="alert alert-info">Nenhum pedido encontrado.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pedido #</th>
                                <th>Cliente</th>
                                <th>Mesa</th>
                                <th>Garçom</th>
                                <th>Status</th>
                                <th>Pagamento</th>
                                <th>Total</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td><strong>#<?php echo $pedido['id']; ?></strong></td>
                                    <td><?php echo $pedido['cliente_nome']; ?></td>
                                    <td><?php echo $pedido['mesa_numero']; ?></td>
                                    <td><?php echo $pedido['garcom_nome'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($pedido['status'] === 'aberto'): ?>
                                            <span class="badge bg-warning">Aberto</span>
                                        <?php elseif ($pedido['status'] === 'finalizado'): ?>
                                            <span class="badge bg-success">Finalizado</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $pedido['forma_pagamento'] ?? '-'; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $total = $pedido_obj->obterTotal($pedido['id']);
                                        echo 'R$ ' . number_format($total, 2, ',', '.'); 
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2 justify-content-start">
                                            <a
                                                href="visualizar_pedidos.php?pedido_id=<?php echo $pedido['id']; ?>"
                                                class="btn btn-sm btn-info"
                                            >
                                                Detalhes
                                            </a>

                                            <?php if ($pedido['status'] === 'aberto'): ?>
                                                <a
                                                    href="adicionar_itens.php?pedido_id=<?php echo $pedido['id']; ?>"
                                                    class="btn btn-sm btn-primary"
                                                >
                                                    Adicionar Itens
                                                </a>
                                                <form method="POST" style="display:inline-block;">
                                                    <input
                                                        type="hidden"
                                                        name="pedido_id"
                                                        value="<?php echo $pedido['id']; ?>"
                                                    >

                                                    <select
                                                        name="forma_pagamento"
                                                        class="form-select form-select-sm mb-1"
                                                        required
                                                    >
                                                        <option value="">Pagamento</option>
                                                        <option value="Dinheiro">Dinheiro</option>
                                                        <option value="Cartao Credito">Cartão Crédito</option>
                                                        <option value="Cartao Debito">Cartão Débito</option>
                                                        <option value="Pix">Pix</option>
                                                    </select>

                                                    <button
                                                        type="submit"
                                                        name="finalizar_pedido"
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        Finalizar
                                                    </button>
                                                </form>
                                                <a
                                                    href="visualizar_pedidos.php?cancelar=<?php echo $pedido['id']; ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Deseja cancelar este pedido?')"
                                                >
                                                    Cancelar
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="criar_pedido.php" class="btn btn-success">Novo Pedido</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>