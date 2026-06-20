<?php
// Classe Pedido para gerenciar pedidos
require_once '../includes/config.php';

class Pedido {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Criar um novo pedido
    public function criarPedido($cliente_id, $mesa_id, $garcom_id) {

    // cria pedido
    $stmt = $this->pdo->prepare(
        "INSERT INTO pedidos (cliente_id, mesa_id, garcom_id)
         VALUES (?, ?, ?)"
    );

    $stmt->execute([$cliente_id, $mesa_id, $garcom_id]);

    $pedido_id = $this->pdo->lastInsertId();

    // ocupa mesa
    $stmt = $this->pdo->prepare(
        "UPDATE mesas
         SET status = 'ocupada'
         WHERE id = ?"
    );

    $stmt->execute([$mesa_id]);

    return $pedido_id;
}

    // Adicionar item ao pedido
    public function adicionarItem($pedido_id, $produto_id, $quantidade) {
        // Verificar estoque
        $stmt = $this->pdo->prepare("SELECT estoque, preco FROM produtos WHERE id = ?");
        $stmt->execute([$produto_id]);
        $produto = $stmt->fetch();

        if (!$produto || $produto['estoque'] < $quantidade) {
            throw new Exception("Estoque insuficiente para o produto.");
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pedido_id, $produto_id, $quantidade, $produto['preco']]);

            $stmt = $this->pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?");
            $stmt->execute([$quantidade, $produto_id]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        // Atualizar total do pedido
        $this->atualizarTotal($pedido_id);
    }

    // Atualizar total do pedido (apenas cálculo, sem persistência)
    private function atualizarTotal($pedido_id) {
    }

    // Obter itens do pedido
    public function obterItens($pedido_id) {
        $stmt = $this->pdo->prepare("
            SELECT ip.*, p.nome, p.preco, (ip.quantidade * ip.preco_unitario) as subtotal
            FROM itens_pedido ip
            JOIN produtos p ON ip.produto_id = p.id
            WHERE ip.pedido_id = ?
        ");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll();
    }

    // Obter total do pedido (calculado dinamicamente)
    public function obterTotal($pedido_id) {
        $stmt = $this->pdo->prepare("SELECT SUM(quantidade * preco_unitario) as total FROM itens_pedido WHERE pedido_id = ?");
        $stmt->execute([$pedido_id]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    // Finalizar pedido
public function finalizarPedido( $pedido_id, $forma_pagamento)
{
    // Buscar a mesa do pedido
    $stmt = $this->pdo->prepare(
        "SELECT mesa_id
         FROM pedidos
         WHERE id = ?"
    );

    $stmt->execute([$pedido_id]);

    $pedido = $stmt->fetch();

    // Finalizar pedido
    $stmt = $this->pdo->prepare(
        "UPDATE pedidos
         SET status = 'finalizado',
             forma_pagamento = ?
         WHERE id = ?"
    );

    $stmt->execute([
        $forma_pagamento,
        $pedido_id
    ]);

    // Liberar mesa
    $stmt = $this->pdo->prepare(
        "UPDATE mesas
         SET status = 'livre'
         WHERE id = ?"
    );

    $stmt->execute([
        $pedido['mesa_id']
    ]);

    return true;
}

//CAncelar pedido
public function cancelarPedido($pedido_id) {

    // Buscar a mesa do pedido
    $stmt = $this->pdo->prepare(
        "SELECT mesa_id
         FROM pedidos
         WHERE id = ?"
    );

    $stmt->execute([$pedido_id]);

    $pedido = $stmt->fetch();

    // Atualizar status do pedido
    $stmt = $this->pdo->prepare(
        "UPDATE pedidos
         SET status = 'cancelado'
         WHERE id = ?"
    );

    $stmt->execute([$pedido_id]);

    // Liberar mesa
    $stmt = $this->pdo->prepare(
        "UPDATE mesas
         SET status = 'livre'
         WHERE id = ?"
    );

    $stmt->execute([$pedido['mesa_id']]);
}

    // Obter pedidos ativos
    public function obterPedidosAtivos() {
        $stmt = $this->pdo->query("SELECT * FROM pedidos WHERE status = 'aberto'");
        return $stmt->fetchAll();
    }
}
?>