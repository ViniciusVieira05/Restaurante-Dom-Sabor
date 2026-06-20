<?php
require_once 'auth.php';

if ($_SESSION['perfil'] != 'admin') {
    die("Acesso negado!");
}
?>