<?php
require_once '../includes/config.php';
require_once '../php/Pedido.php';
require_once '../includes/auth.php';

$pedido = new Pedido();
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    
    // CRIAR PEDIDO
    if (isset($_POST['criar_pedido'])) {

        $cliente_id = $_POST['cliente_id'] ?? '';
        $mesa_id = $_POST['mesa_id'] ?? '';
        $garcom_id = isGarcom() ? $_SESSION['id'] : ($_POST['garcom_id'] ?? null);

        try {
            if (empty($cliente_id) || !is_numeric($cliente_id)) {
                throw new Exception("Selecione um cliente válido.");
            }

            if (empty($mesa_id) || !is_numeric($mesa_id)) {
                throw new Exception("Selecione uma mesa válida.");
            }

            if (empty($garcom_id) || !is_numeric($garcom_id)) {
                throw new Exception("Selecione um garçom para este pedido.");
            }

            
            $pedido_id = $pedido->criarPedido(
                $cliente_id,
                $mesa_id,
                $garcom_id
            );

            header(
                "Location: criar_pedido.php?pedido_id=$pedido_id"
            );

            exit;

        } catch (Exception $e) {
            $erro = $e->getMessage();
        }

    }

    // ADICIONAR ITEM
    elseif (isset($_POST['adicionar_item'])) {

        $pedido_id = $_POST['pedido_id'];
        $produto_id = $_POST['produto_id'];
        $quantidade = $_POST['quantidade'];

        try {

            $pedido->adicionarItem(
                $pedido_id,
                $produto_id,
                $quantidade
            );

            $mensagem = 'Item adicionado com sucesso!';

        } catch (Exception $e) {

            $erro = $e->getMessage();
        }
    }

    // FINALIZAR PEDIDO
        elseif (isset($_POST['finalizar_pedido'])) {

            $pedido_id = $_POST['pedido_id'];
            $forma_pagamento = $_POST['forma_pagamento'];

            try {

                $pedido->finalizarPedido(
                    $pedido_id,
                    $forma_pagamento
                );

            $mensagem = 'Pedido finalizado com sucesso!';

        } catch (Exception $e) {

                $erro = $e->getMessage();
            }
        }
    }

    $pedido_id = $_GET['pedido_id'] ?? null;
    $itens = $pedido_id ? $pedido->obterItens($pedido_id) : [];
    $total = $pedido_id ? $pedido->obterTotal($pedido_id) : 0;

    // Obter listas para selects
    $clientes = $pdo->query("SELECT * FROM clientes")->fetchAll();
    $mesas = $pdo->query("SELECT * FROM mesas WHERE status = 'livre'")->fetchAll();
    $garcons = $pdo->query("SELECT * FROM usuarios WHERE perfil = 'garcom' ")->fetchAll();
    $produtos = $pdo->query("SELECT * FROM produtos")->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Criar Pedido - Restaurante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container my-5">
        <h1>Criar Pedido</h1>

        <?php if ($mensagem): ?>
            <div class="alert alert-success"><?php echo $mensagem; ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if (!$pedido_id): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">
                        Cliente Existente
                    </label>

                    <select class="form-select" id="cliente_id" name="cliente_id">
                        <option value="">Selecione um cliente</option>

                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id']; ?>">
                                <?php echo $cliente['nome']; ?> - <?php echo $cliente['cpf']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Cliente não cadastrado?
                    </label>
                    <a href="clientes.php" class="btn btn-outline-primary w-100">
                        Ir para Clientes
                    </a>
                </div>
                <div class="mb-3">
                    <label for="mesa_id" class="form-label">Mesa</label>
                    <select class="form-select" id="mesa_id" name="mesa_id" required>
                        <option value="">Selecione uma mesa</option>
                        <?php foreach ($mesas as $mesa): ?>
                            <option value="<?php echo $mesa['id']; ?>">Mesa <?php echo $mesa['numero']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!isGarcom()): ?>

                    <div class="mb-3">
                        <label for="garcom_id" class="form-label">
                            Garçom
                        </label>

                        <select
                            class="form-select"
                            id="garcom_id"
                            name="garcom_id"
                            required
                        >
                            <option value="">
                                Selecione um garçom
                            </option>

                            <?php foreach ($garcons as $garcom): ?>
                                <option value="<?php echo $garcom['id']; ?>">
                                    <?php echo $garcom['nome']; ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label">Garçom</label>
                        <input type="text" class="form-control" value="<?php echo $_SESSION['nome']; ?>" readonly>
                    </div>
                <?php endif; ?>
                <button type="submit" name="criar_pedido" class="btn btn-primary">Criar Pedido</button>
            </form>
        <?php else: ?>
            <h3>Pedido #<?php echo $pedido_id; ?></h3>

            <form method="POST" class="mb-4">
                <input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">
                <div class="row">
                    <div class="col-md-6">
                        <label for="produto_id" class="form-label">Produto</label>
                        <select class="form-select" id="produto_id" name="produto_id" required>
                            <option value="">Selecione um produto</option>
                            <?php foreach ($produtos as $produto): ?>
                                <option value="<?php echo $produto['id']; ?>"><?php echo $produto['nome']; ?> - R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?> (Estoque: <?php echo $produto['estoque']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" name="adicionar_item" class="btn btn-success w-100">Adicionar Item</button>
                    </div>
                </div>
            </form>

            <h4>Itens do Pedido</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Preço Unitário</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <tr>
                            <td><?php echo $item['nome']; ?></td>
                            <td><?php echo $item['quantidade']; ?></td>
                            <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h4>Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></h4>

            <form method="POST" class="mt-3">

                <input
                    type="hidden"
                    name="pedido_id"
                    value="<?php echo $pedido_id; ?>"
                >

                <div class="mb-3">
                    <label class="form-label">
                        Forma de Pagamento
                    </label>

                    <select
                        class="form-select"
                        name="forma_pagamento"
                        required
                    >
                        <option value="">
                            Selecione
                        </option>

                        <option value="Dinheiro">
                            Dinheiro
                        </option>

                        <option value="Cartao Credito">
                            Cartão de Crédito
                        </option>

                        <option value="Cartao Debito">
                            Cartão de Débito
                        </option>

                        <option value="Pix">
                            Pix
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    name="finalizar_pedido"
                    class="btn btn-primary"
                >
                    Finalizar Pedido
                </button>

            </form>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>