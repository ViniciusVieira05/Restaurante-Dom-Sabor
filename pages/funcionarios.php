<?php require_once '../includes/config.php';
require_once '../includes/auth.php';

if ($_SESSION['perfil'] != 'admin') {
    die("Acesso negado.");
}
$mensagem = '';
$erro = '';
if (isset($_POST['cadastrar'])) {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha = md5($_POST['senha']);
    $cpf = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);
    $cargo = trim($_POST['cargo']);
    $salario = $_POST['salario'];
    $data_admissao = $_POST['data_admissao'];
    try {
        if (!preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf)) {
            throw new Exception("CPF deve ser no formato XXX.XXX.XXX-XX.");
        }
        if (!preg_match('/^\(\d{2}\) \d{5}-\d{4}$/', $telefone)) {
            throw new Exception("Telefone deve ser no formato (XX) XXXXX-XXXX.");
        }
        $verifica = $pdo->prepare(" SELECT id FROM usuarios WHERE usuario = ? ");
        $verifica->execute([$usuario]);
        $stmt = $pdo->prepare(" SELECT id FROM funcionarios WHERE cpf = ? ");
        $stmt->execute([$cpf]);
        $cpfNumeros = preg_replace('/\D/', '', $cpf);
        if (strlen($cpfNumeros) != 11) {
            throw new Exception("CPF deve ser no formato XXX.XXX.XXX-XX.");
        }
        $telefoneNumeros = preg_replace('/\D/', '', $telefone);
        if (strlen($telefoneNumeros) != 10 && strlen($telefoneNumeros) != 11) {
            throw new Exception("Telefone deve ser no formato (XX) XXXXX-XXXX.");
        }
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(" INSERT INTO usuarios (nome, usuario, senha, perfil) VALUES (?, ?, ?, 'garcom') ");
        $stmt->execute([$nome, $usuario, $senha]);
        $usuario_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare(" INSERT INTO funcionarios ( usuario_id, nome, cpf, telefone, cargo, salario, data_admissao ) VALUES ( ?,?,?,?,?,?,? ) ");
        $stmt->execute([$usuario_id, $nome, $cpf, $telefone, $cargo, $salario, $data_admissao]);
        $pdo->commit();
        $mensagem = "Funcionário cadastrado com sucesso!";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $erro = $e->getMessage();
    }
}


if (isset($_GET['excluir'])) {
    if ($_SESSION['perfil'] != 'admin') {
        die("Acesso negado.");
    }
    try {
        $id = intval($_GET['excluir']);
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE garcom_id = ?");
        $stmt->execute([$id]);
        $pedidosCount = $stmt->fetchColumn();
        if ($pedidosCount > 0) {
            throw new Exception("Não é possível excluir este funcionário porque ele possui pedidos registrados.");
        }
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND perfil = 'garcom'");
        $stmt->execute([$id]);
        $mensagem = "Funcionário excluído com sucesso!";
    } catch (Exception $e) {
        $erro = $e->getMessage() ?: "Não foi possível excluir este funcionário.";
    }
} ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Funcionários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>

<body> <?php include '../includes/header.php'; ?>
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="mb-4"> Cadastro de Funcionários </h2> <?php if ($mensagem): ?>
                <div class="alert alert-success"> <?php echo $mensagem; ?> </div> <?php endif; ?> <?php if ($erro): ?>
                <div class="alert alert-danger"> <?php echo $erro; ?> </div> <?php endif; ?>
            <form method="POST">
                <div class="mb-3"> <label class="form-label"> Nome </label> <input type="text" name="nome"
                        class="form-control" required>
                    <div class="mb-3"> <label>CPF</label> <input type="text" id="cpf" name="cpf" class="form-control"
                            maxlength="14" required> </div>
                    <div class="mb-3"> <label>Telefone</label> <input type="text" id="telefone" name="telefone"
                            class="form-control" maxlength="15" required> </div>
                    <div class="mb-3"> <label>Cargo</label> <select name="cargo" class="form-select" required>
                            <option value="garcom">Garçom</option>
                            <option value="caixa">Caixa</option>
                            <option value="cozinheiro">Cozinheiro</option>
                            <option value="gerente">Gerente</option>
                        </select> </div>
                    <div class="mb-3"> <label>Salário</label> <input type="number" step="0.01" name="salario"
                            class="form-control" required> </div>
                    <div class="mb-3"> <label>Data de Admissão</label> <input type="date" name="data_admissao"
                            class="form-control" value="<?php echo date('Y-m-d'); ?>" required> </div>
                </div>
                <div class="mb-3"> <label class="form-label"> Usuário </label> <input type="text" name="usuario"
                        class="form-control" required> </div>
                <div class="mb-3"> <label class="form-label"> Senha </label> <input type="password" name="senha"
                        class="form-control" required> </div> <button type="submit" name="cadastrar"
                    class="btn btn-success"> Cadastrar Funcionário </button>
            </form>
            <hr class="my-4">
            <h3>Funcionários Cadastrados</h3>
            <?php $funcionarios = $pdo->query(" SELECT u.id AS usuario_id, u.usuario, g.id, g.nome, g.cpf, g.telefone, g.cargo, g.salario, g.status, g.data_admissao FROM funcionarios g INNER JOIN usuarios u ON u.id = g.usuario_id ORDER BY g.nome ")->fetchAll(); ?>
            <table class="table table-striped mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Usuário</th>
                        <th>Salário</th>
                        <th>Cargo</th>
                        <th>Admissão</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody> <?php foreach ($funcionarios as $funcionario): ?>
                        <tr>
                            <td> FUNC-<?php echo str_pad($funcionario['id'], 3, '0', STR_PAD_LEFT); ?> </td>
                            <td>
                                <?php echo $funcionario['nome']; ?>
                            </td>
                            <td>
                                <?php echo $funcionario['cpf']; ?>
                            </td>
                            <td>
                                <?php echo $funcionario['telefone']; ?>
                            </td>
                            <td>
                                <?php echo $funcionario['usuario']; ?>
                            </td>
                            <td> R$ 
                                <?php echo number_format($funcionario['salario'], 2, ',', '.'); ?> 
                            </td>
                            <td> 
                                <?php echo $funcionario['cargo']; ?> 
                            </td>
                            <td> 
                                <?php echo date('d/m/Y', strtotime($funcionario['data_admissao'])); ?> 
                            </td>
                            <td> 
                                <?php if ($funcionario['status'] == 'ativo'): ?> <span class="badge bg-success"> Ativo
                                    </span>
                                <?php else: ?> <span class="badge bg-danger"> Inativo </span> <?php endif; ?> </td>
                            <td> <a href="editar_funcionario.php?id=<?php echo $funcionario['id']; ?>"
                                    class="btn btn-warning btn-sm"> Editar </a> <a
                                    href="?excluir=<?php echo $funcionario['usuario_id']; ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Excluir este funcionário?')"> Excluir </a> </td>
                        </tr> <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div> <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script> const cpf = document.getElementById('cpf'); if (cpf) { cpf.addEventListener('input', function (e) { let valor = e.target.value.replace(/\D/g, ''); valor = valor.replace(/(\d{3})(\d)/, "$1.$2"); valor = valor.replace(/(\d{3})(\d)/, "$1.$2"); valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2"); e.target.value = valor; }); } const telefone = document.getElementById('telefone'); if (telefone) { telefone.addEventListener('input', function (e) { let valor = e.target.value.replace(/\D/g, ''); valor = valor.replace(/^(\d{2})(\d)/g, "($1) $2"); valor = valor.replace(/(\d)(\d{4})$/, "$1-$2"); e.target.value = valor; }); } </script>
</body>

</html>