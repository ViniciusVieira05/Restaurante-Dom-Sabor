<?php
session_start();

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

function isAdmin() {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'admin';
}

function isGarcom() {
    return isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'garcom';
}

function pedidoPertenceAoGarcom($pdo, $pedido_id) {
    if (!isGarcom()) {
        return true;
    }

    $stmt = $pdo->prepare("SELECT garcom_id FROM pedidos WHERE id = ?");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();

    return $pedido && $pedido['garcom_id'] == $_SESSION['id'];
}
