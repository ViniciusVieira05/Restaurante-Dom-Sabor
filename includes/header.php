    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
           <?php if ($_SESSION['perfil'] == 'admin'): ?>
                <a class="navbar-brand" href="../index.html">Dom Sabor</a>
            <?php else: ?>
                <a class="navbar-brand" href="criar_pedido.php">Dom Sabor</a>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
           <?php if ($_SESSION['perfil'] == 'admin'): ?>
                <a class="navbar-brand" href="../index.html">Dom Sabor</a>
            <?php else: ?>
                <a class="navbar-brand" href="criar_pedido.php">Dom Sabor</a>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($_SESSION['perfil'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">Início</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="criar_pedido.php">Criar Pedido</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adicionar_itens.php">Adicionar Itens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="visualizar_pedidos.php">Ver Pedidos</a>
                    </li>

                    <?php if ($_SESSION['perfil'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="funcionarios.php">Cadastro de Funcionários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="despesas.php">Despesas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorio_vendas.php">Relatório de Vendas</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cardapio.php">
                            Cardápio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clientes.php">Clientes</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-light btn-sm" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if ($_SESSION['perfil'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">Início</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="criar_pedido.php">Criar Pedido</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="adicionar_itens.php">Adicionar Itens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="visualizar_pedidos.php">Ver Pedidos</a>
                    </li>

                    <?php if ($_SESSION['perfil'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cadastro_garcom.php">Cadastro Garçom</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="despesas.php">Despesas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorio_vendas.php">Relatório de Vendas</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cardapio.php">
                            Cardápio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clientes.php">Clientes</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-light btn-sm" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>