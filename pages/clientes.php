<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';

$mensagem = '';
$erro = '';

// CADASTRAR CLIENTE
if(isset($_POST['cadastrar'])){

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);

    try{

        if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
            throw new Exception("CPF deve ser no formato XXX.XXX.XXX-XX.");
        }

        if (!preg_match('/^\(\d{2}\) \d{5}-\d{4}$/', $telefone)) {
            throw new Exception("Telefone deve ser no formato (XX) XXXXX-XXXX.");
        }

        // Verifica CPF duplicado
        $verifica = $pdo->prepare("
            SELECT id
            FROM clientes
            WHERE cpf = ?
        ");

        $verifica->execute([$cpf]);

        if($verifica->rowCount() > 0){
            throw new Exception("Já existe um cliente com este CPF.");
        }

        $stmt = $pdo->prepare("
            INSERT INTO clientes
            (nome, cpf, telefone)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $nome,
            $cpf,
            $telefone
        ]);

        $mensagem = "Cliente cadastrado com sucesso!";

    }catch(Exception $e){

        $erro = $e->getMessage();
    }
}

// EXCLUIR CLIENTE
if(isset($_GET['excluir'])){

    if($_SESSION['perfil'] != 'admin'){
        die("Acesso negado.");
    }

    try{

        $id = intval($_GET['excluir']);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE cliente_id = ?");
        $stmt->execute([$id]);
        $pedidosCount = $stmt->fetchColumn();

        if($pedidosCount > 0){
            throw new Exception("Não é possível excluir este cliente porque ele possui pedidos no sistema.");
        }

        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);

        $mensagem = "Cliente excluído com sucesso!";

    }catch(Exception $e){

        $erro = $e->getMessage() ?: "Não foi possível excluir este cliente.";
    }
}

// LISTAR CLIENTES
$clientes = $pdo->query("
    SELECT *
    FROM clientes
    ORDER BY nome
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">

    <title>Clientes</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="../css/style.css"
        rel="stylesheet"
    >

</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">

    <div class="card shadow p-4">

        <h2 class="mb-4">
            Cadastro de Clientes
        </h2>

        <?php if($mensagem): ?>
            <div class="alert alert-success">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if($erro): ?>
            <div class="alert alert-danger">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row">

                <div class="col-md-4">

                    <label class="form-label">
                        Nome
                    </label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        CPF
                    </label>

                    <input
                        type="text"
                        name="cpf"
                        class="form-control"
                        placeholder="XXX.XXX.XXX-XX"
                        pattern="\d{3}\.\d{3}\.\d{3}-\d{2}"
                        title="Formato: XXX.XXX.XXX-XX"
                        required
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Telefone
                    </label>

                    <input
                        type="text"
                        name="telefone"
                        class="form-control"
                        placeholder="(XX) XXXXX-XXXX"
                        pattern="\(\d{2}\) \d{5}-\d{4}"
                        title="Formato: (XX) XXXXX-XXXX"
                        required
                    >

                </div>

            </div>

            <button
                type="submit"
                name="cadastrar"
                class="btn btn-success mt-3"
            >
                Cadastrar Cliente
            </button>

        </form>

    </div>

    <?php if($_SESSION['perfil'] == 'admin'): ?>

        <div class="card shadow mt-4">
            <div class="card-body">

                <h3>
                    Clientes Cadastrados
                </h3>

                <?php if(empty($clientes)): ?>

                    <div class="alert alert-info mt-3">
                        Nenhum cliente cadastrado.
                    </div>

                <?php else: ?>

                    <div class="table-responsive mt-3">

                        <table class="table table-striped table-hover">

                            <thead class="table-dark">

                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach($clientes as $cliente): ?>

                                    <tr>

                                        <td>
                                            <?php echo $cliente['id']; ?>
                                        </td>

                                        <td>
                                            <?php echo $cliente['nome']; ?>
                                        </td>

                                        <td>
                                            <?php echo $cliente['cpf']; ?>
                                        </td>

                                        <td>
                                            <?php echo $cliente['telefone']; ?>
                                        </td>

                                        <td>

                                            <a
                                                href="clientes.php?excluir=<?php echo $cliente['id']; ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Deseja excluir este cliente?')"
                                            >
                                                Excluir
                                            </a>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>
        <?php endif; ?>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>