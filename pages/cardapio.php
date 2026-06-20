<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';

$mensagem = '';

if(isset($_POST['cadastrar'])){

    $nome = $_POST['nome'];
    $categoria = $_POST['categoria'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];

    $stmt = $pdo->prepare("
        INSERT INTO produtos
        (nome, categoria, descricao, preco, estoque)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $nome,
        $categoria,
        $descricao,
        $preco,
        $estoque
    ]);

    $mensagem = "Prato cadastrado com sucesso!";
}


$produtos = $pdo->query("
    SELECT *
    FROM produtos
    ORDER BY categoria, nome
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <title>Cardápio</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link href="../css/style.css" rel="stylesheet">

</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container my-5">

    <h1 class="mb-4">
        Cardápio
    </h1>

    <?php if($mensagem): ?>
    <div class="alert alert-success">
        <?php echo $mensagem; ?>
    </div>
    <?php endif; ?>

    <?php if($_SESSION['perfil'] == 'admin'): ?>

    <div class="card shadow mb-4">
        <div class="card-body">

            <h3>Cadastrar Novo Prato</h3>

            <form method="POST">

                <div class="row">

                    <div class="col-md-4">
                        <label>Nome</label>
                        <input
                            type="text"
                            name="nome"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="col-md-3">
                        <label>Categoria</label>
                        <input
                            type="text"
                            name="categoria"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="col-md-2">
                        <label>Preço</label>
                        <input
                            type="number"
                            step="0.01"
                            name="preco"
                            class="form-control"
                            required
                        >
                    </div>

                    <div class="col-md-2">
                        <label>Estoque</label>
                        <input
                            type="number"
                            name="estoque"
                            class="form-control"
                            required
                        >
                    </div>

                </div>

                <div class="mt-3">

                    <label>Descrição</label>

                    <textarea
                        name="descricao"
                        class="form-control"
                        rows="3"
                    ></textarea>

                </div>

                <button
                    type="submit"
                    name="cadastrar"
                    class="btn btn-success mt-3"
                >
                    Cadastrar Prato
                </button>

            </form>

        </div>
    </div>

    <?php endif; ?>

    <div class="row">

        <?php foreach($produtos as $produto): ?>

            <div class="col-md-4 mb-4">

                <div class="card h-100 shadow">

                    <div class="card-body">

                        <h4>
                            <?php echo $produto['nome']; ?>
                        </h4>

                        <p class="text-muted">
                            <?php echo $produto['categoria']; ?>
                        </p>

                        <p>
                            <?php echo $produto['descricao']; ?>
                        </p>

                        <h5 class="text-success">
                            R$ <?php echo number_format($produto['preco'],2,',','.'); ?>
                        </h5>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

</body>
</html>