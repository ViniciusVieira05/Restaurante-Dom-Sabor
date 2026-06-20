# Dom Sabor - Sistema de Gerenciamento para Restaurantes

<p align="center">

Sistema desenvolvido em **PHP + MySQL** para gerenciamento completo de restaurantes, permitindo o controle de pedidos, mesas, garçons, clientes, cardápio, despesas e relatórios administrativos.

Projeto desenvolvido como projeto acadêmico do curso de **Análise e Desenvolvimento de Sistemas**.

</p>

---
# Funcionalidades

## Login

- Login de usuários
- Controle de acesso por perfil
- Administrador
- Garçom

---

## Administração

- Dashboard
- Cadastro de clientes
- Cadastro de garçons
- Cadastro de produtos
- Cadastro de despesas
- Relatórios
- Controle de estoque

---

## Pedidos

- Criar pedido
- Selecionar mesa
- Selecionar cliente
- Selecionar garçom
- Adicionar produtos
- Finalizar pedido
- Cancelar pedido

---

## Mesas

- Controle automático de ocupação
- Liberação automática após finalizar pedido
- Status:
  - Livre
  - Ocupada

---

## Cardápio

- Cadastro de pratos
- Cadastro de bebidas
- Cadastro de sobremesas
- Controle de disponibilidade

---

## Clientes

- Cadastro
- Consulta
- Exclusão

---

## Garçons

Cada garçom possui:

- Nome
- CPF
- Telefone
- Salário
- Data de admissão
- Status

Além disso, cada garçom possui um usuário de acesso ao sistema.

---

## Dashboard

O sistema apresenta indicadores em tempo real:

- Total de pedidos
- Pedidos abertos
- Pedidos finalizados
- Pedidos cancelados
- Clientes cadastrados
- Produtos cadastrados
- Mesas ocupadas
- Faturamento bruto
- Total de despesas
- Faturamento líquido
- Faturamento do dia
- Ranking de garçons

---

## Relatórios

- Relatório de vendas
- Total vendido
- Vendas por período
- Vendas por garçom

---

# 🛠 Tecnologias utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- XAMPP

---

# Estrutura do projeto

```
Restaurante/
│
├── css/
│   └── style.css
|
├── includes/
│   ├── admin.php
│   ├── auth.php
│   ├── config.php
│   ├── footer.php
│   └── header.php
│
├── js/
│   └── script.js
│
├── pages/
│   ├── adicionar_itens.php
│   ├── cadastro_garcom.php
│   ├── cardapio.php
│   ├── clientes.php
│   ├── criar_pedidos.php
│   ├── dashboard.php
│   ├── despesas.php
│   ├── editaar_garcom.php
│   ├── login.php
│   ├── logout.php
│   ├── relatorio_vendas.php
│   └── visualizar_pedidos.php
│
├── php/
│   └── Pedido.php
│
├── index.html
│
└── README.md
```

---

# Banco de Dados

O sistema utiliza **MySQL**.

Principais tabelas:

- usuarios
- garcons
- clientes
- produtos
- pedidos
- itens_pedido
- mesas
- despesas

---

# Controle de usuários

## Administrador

Pode:

- cadastrar garçons
- cadastrar produtos
- cadastrar clientes
- visualizar dashboard
- gerar relatórios
- cadastrar despesas
- controlar pedidos

---

## Garçom

Pode:

- criar pedidos
- adicionar itens
- finalizar pedidos
- visualizar pedidos

---
# Conceitos aplicados

- CRUD
- Programação Orientada a Objetos
- Sessões
- Autenticação
- Controle de acesso
- Relacionamentos no banco
- Chaves primárias
- Chaves estrangeiras
- Prepared Statements
- Bootstrap
- Responsividade
- Arquitetura modular

---

# Melhorias futuras

- Upload de imagens dos pratos
- Impressão de comandas
- Controle financeiro completo
- Delivery
- Reserva de mesas
- QR Code para cardápio
- Gráficos estatísticos
- API REST
- Aplicativo mobile

---

# Autor

**Vinicius Vieira Romão**

Graduando em **Análise e Desenvolvimento de Sistemas**

Instituto Federal do Piauí (IFPI)
---

Este projeto foi desenvolvido para fins acadêmicos e de aprendizado.
