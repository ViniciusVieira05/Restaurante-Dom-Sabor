<?php

session_start();
require_once '../includes/config.php';

if(isset($_POST['entrar'])){

    $usuario = $_POST['usuario'];
    $senha = md5($_POST['senha']);

    $sql = $pdo->prepare("
        SELECT *
        FROM usuarios
        WHERE usuario = ?
        AND senha = ?
    ");

    $sql->execute([$usuario, $senha]);

    if($sql->rowCount() > 0){

        $dados = $sql->fetch();

        $_SESSION['id'] = $dados['id'];
        $_SESSION['nome'] = $dados['nome'];
        $_SESSION['perfil'] = $dados['perfil'];

       if ($dados['perfil'] == 'admin') {
            header("Location: ../index.html");
        } else {
            header("Location: criar_pedido.php");
        }

        exit;
    }

    $erro = "Usuário ou senha inválidos";
}

$usuarios = $pdo->query("SELECT usuario FROM usuarios")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Login</h2>

                        <?php if(isset($erro)): ?>
                            <div class="alert alert-danger"><?php echo $erro; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuário</label>
                                <select class="form-select" id="usuario" name="usuario" required>
                                    <option value="">Selecione um usuário</option>
                                    <?php foreach ($usuarios as $usuario_item): ?>
                                        <option value="<?php echo $usuario_item['usuario']; ?>"><?php echo $usuario_item['usuario']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>
                            <button type="submit" name="entrar" class="btn btn-primary w-100">Entrar</button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-muted mt-3">Restaurante Dom Sabor</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>