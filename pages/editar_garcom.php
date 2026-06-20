<?php

require_once '../includes/config.php';
require_once '../includes/auth.php';

if($_SESSION['perfil'] != 'admin'){
    die("Acesso negado.");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
SELECT
g.*,
u.usuario
FROM garcons g
INNER JOIN usuarios u
ON u.id = g.usuario_id
WHERE g.id = ?

");

$stmt->execute([$id]);

$garcom = $stmt->fetch();

if(!$garcom){
    die("Garçom não encontrado.");
}

$mensagem = "";
$erro = "";

if(isset($_POST['salvar'])){

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);
    $salario = $_POST['salario'];
    $status = $_POST['status'];
    $usuario = trim($_POST['usuario']);

    try{

        $cpfNumeros = preg_replace('/\D/', '', $cpf);

        if(strlen($cpfNumeros) != 11){
            throw new Exception("CPF deve ser no formato XXX.XXX.XXX-XX.");
        }

        $telefoneNumeros = preg_replace('/\D/', '', $telefone);

        if(strlen($telefoneNumeros) != 10 && strlen($telefoneNumeros) != 11){
            throw new Exception("Telefone deve ser no formato (XX) XXXXX-XXXX.");
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
        UPDATE garcons
        SET

        nome=?,
        cpf=?,
        telefone=?,
        salario=?,
        status=?

        WHERE id=?
        ");

        $stmt->execute([

            $nome,
            $cpf,
            $telefone,
            $salario,
            $status,
            $id

        ]);

        $stmt = $pdo->prepare("
        UPDATE usuarios
        SET

        nome=?,
        usuario=?

        WHERE id=?
        ");

        $stmt->execute([

            $nome,
            $usuario,
            $garcom['usuario_id']

        ]);

        $pdo->commit();

        $mensagem="Garçom atualizado com sucesso.";

    }catch(Exception $e){

         $erro = $e->getMessage();

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Editar Garçom</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="../css/style.css"
rel="stylesheet">

</head>

<body>

<?php include '../includes/header.php'; ?>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-dark text-white">

<h3 class="mb-0">
Editar Garçom
</h3>

</div>

<div class="card-body">

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

<div class="col-md-6 mb-3">

<label>Nome</label>

<input
type="text"
name="nome"
class="form-control"
value="<?php echo $garcom['nome']; ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>CPF</label>

<input
type="text"
name="cpf"
class="form-control"
value="<?php echo $garcom['cpf']; ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label>Telefone</label>

<input
type="text"
name="telefone"
class="form-control"
value="<?php echo $garcom['telefone']; ?>">

</div>


<div class="col-md-3 mb-3">

<label>Salário</label>

<input
type="number"
step="0.01"
name="salario"
class="form-control"
value="<?php echo $garcom['salario']; ?>">

</div>

<div class="col-md-3 mb-3">

<label>Status</label>

<select
name="status"
class="form-select">

<option
value="ativo"
<?php if($garcom['status']=="ativo") echo "selected"; ?>>

Ativo

</option>

<option
value="inativo"
<?php if($garcom['status']=="inativo") echo "selected"; ?>>

Inativo

</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Usuário</label>

<input
type="text"
name="usuario"
class="form-control"
value="<?php echo $garcom['usuario']; ?>">

</div>

<div class="col-md-6 mb-3">

<label>Nova Senha (opcional)</label>

<input
type="password"
name="senha"
class="form-control">

<small class="text-muted">

Deixe em branco para manter a senha atual.

</small>

</div>

</div>

<div class="mt-3">

<button
class="btn btn-success"
type="submit"
name="salvar">

Salvar Alterações

</button>

<a
href="cadastro_garcom.php"
class="btn btn-secondary">

Voltar

</a>

</div>

</form>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>