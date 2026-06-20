<?php
require_once '../includes/config.php';
require_once '../php/Pedido.php';
require_once '../includes/auth.php';

$pedido = new Pedido();
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['adicionar_item'])) {
        $pedido_id = $_POST['pedido_id'];
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];

        try {
            if (isGarcom() && !pedidoPertenceAoGarcom($pdo, $pedido_id)) {
                throw new Exception('Pedido não pertence a este garçom.');
            }

            $pedido->adicionarItem($pedido_id, $produto_id, $quantidade);
            $mensagem = 'Item adicionado com sucesso!';
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}

$pedido_id = $_GET['pedido_id'] ?? null;
$itens = $pedido_id ? $pedido->obterItens($pedido_id) : [];
$total = $pedido_id ? $pedido->obterTotal($pedido_id) : 0;

// Obter dados do pedido
$pedido_info = null;
if ($pedido_id) {
    $stmt = $pdo->prepare("SELECT p.*, c.nome as cliente_nome, m.numero as mesa_numero FROM pedidos p 
                           LEFT JOIN clientes c ON p.cliente_id = c.id 
                           LEFT JOIN mesas m ON p.mesa_id = m.id 
                           WHERE p.id = ?");
    $stmt->execute([$pedido_id]);
    $pedido_info = $stmt->fetch();

    if (!$pedido_info || (isGarcom() && !pedidoPertenceAoGarcom($pdo, $pedido_id))) {
        $pedido_info = null;
        $erro = 'Pedido não encontrado ou sem acesso.';
        $itens = [];
        $total = 0;
    }
}

// Obter produtos disponíveis
$produtos = $pdo->query("SELECT * FROM produtos")->fetchAll();

// Obter pedidos abertos para seleção
if (isGarcom()) {
    $stmt = $pdo->prepare("SELECT p.*, c.nome as cliente_nome FROM pedidos p 
                                LEFT JOIN clientes c ON p.cliente_id = c.id 
                                WHERE p.status = 'aberto' AND p.garcom_id = ?");
    $stmt->execute([$_SESSION['id']]);
    $pedidos_abertos = $stmt->fetchAll();
} else {
    $pedidos_abertos = $pdo->query("SELECT p.*, c.nome as cliente_nome FROM pedidos p 
                                LEFT JOIN clientes c ON p.cliente_id = c.id 
                                WHERE p.status = 'aberto'")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Itens - Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container my-5">
        <h1>Adicionar Itens ao Pedido</h1>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if (!$pedido_id): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Selecionar Pedido</h5>
                    <?php if (empty($pedidos_abertos)): ?>
                        <p class="text-danger">Não há pedidos abertos disponíveis.</p>
                        <a href="criar_pedido.php" class="btn btn-primary">Criar Novo Pedido</a>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($pedidos_abertos as $p): ?>
                                <a href="adicionar_itens.php?pedido_id=<?php echo $p['id']; ?>" class="list-group-item list-group-item-action">
                                    <strong>Pedido #<?php echo $p['id']; ?></strong> - Cliente: <?php echo $p['cliente_nome']; ?>
                                    <span class="badge bg-info float-end">Aberto</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Informações do Pedido</h5>
                    <p><strong>Pedido #:</strong> <?php echo $pedido_info['id']; ?></p>
                    <p><strong>Cliente:</strong> <?php echo $pedido_info['cliente_nome']; ?></p>
                    <p><strong>Mesa:</strong> <?php echo $pedido_info['mesa_numero']; ?></p>
                    <p><strong>Status:</strong> <span class="badge bg-warning"><?php echo $pedido_info['status']; ?></span></p>
                </div>
            </div>

            <form method="POST" class="mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Adicionar Item</h5>
                        <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="produto_id" class="form-label">Produto</label>
                                <select class="form-select" id="produto_id" name="produto_id" required>
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                        <option value="<?php echo $produto['id']; ?>">
                                            <?php echo $produto['nome']; ?> - R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                                            (Estoque: <?php echo $produto['estoque']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="quantidade" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" name="adicionar_item" class="btn btn-success w-100">Adicionar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <h4>Itens do Pedido</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($itens)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum item adicionado ainda</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?php echo $item['nome']; ?></td>
                                <td><?php echo $item['quantidade']; ?></td>
                                <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($itens)): ?>
                <div class="alert alert-info">
                    <h5>Total do Pedido: <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></h5>
                </div>

                <div class="mt-3">
                    <a href="visualizar_pedidos.php?pedido_id=<?php echo $pedido_id; ?>" class="btn btn-info">Ver Pedido</a>
                    <a href="criar_pedido.php?pedido_id=<?php echo $pedido_id; ?>" class="btn btn-primary">Finalizar Pedido</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>