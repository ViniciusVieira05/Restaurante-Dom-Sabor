<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';

if($_SESSION['perfil'] != 'admin'){
    die("Acesso negado.");
}

$mensagem = '';
$erro = '';

if(isset($_POST['cadastrar'])){

    $descricao = trim($_POST['descricao']);
    $categoria = trim($_POST['categoria']);
    $valor = $_POST['valor'];
    $data_despesa = $_POST['data_despesa'];

    try{

        $stmt = $pdo->prepare("\n            INSERT INTO despesas\n            (descricao, categoria, valor, data_despesa)\n            VALUES (?, ?, ?, ?)\n        ");

        $stmt->execute([
            $descricao,
            $categoria,
            $valor,
            $data_despesa
        ]);

        $mensagem = "Despesa cadastrada com sucesso!";

    }catch(Exception $e){

        $erro = $e->getMessage();
    }
}

$despesas = $pdo->query("SELECT * FROM despesas ORDER BY data_despesa DESC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Despesas</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">

</head>

<body>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">

<div class="card shadow p-4">

<h2>Cadastrar Despesa</h2>

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

<div class="mb-3">

<label class="form-label">
Descrição
</label>

<input
type="text"
name="descricao"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">
Categoria
</label>

<select
name="categoria"
class="form-select"
required>

<option value="">Selecione</option>

<option value="Funcionários">
Funcionários
</option>

<option value="Fornecedores">
Fornecedores
</option>

<option value="Energia">
Energia
</option>

<option value="Água">
Água
</option>

<option value="Internet">
Internet
</option>

<option value="Aluguel">
Aluguel
</option>

<option value="Outros">
Outros
</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">
Valor
</label>

<input
type="number"
step="0.01"
name="valor"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">
Data
</label>

<input
type="date"
name="data_despesa"
class="form-control"
required>

</div>

<button
type="submit"
name="cadastrar"
class="btn btn-danger">

Cadastrar Despesa

</button>

</form>

</div>

</div>

<div class="container mt-4">
    <div class="card shadow p-4">
        <h2>Despesas Registradas</h2>
        <div class="table-responsive mt-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($despesas)): ?>
                        <?php foreach ($despesas as $d): ?>
                            <tr>
                                <td><?php echo $d['id']; ?></td>
                                <td><?php echo htmlspecialchars($d['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($d['categoria']); ?></td>
                                <td>R$ <?php echo number_format($d['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($d['data_despesa'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Nenhuma despesa cadastrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>