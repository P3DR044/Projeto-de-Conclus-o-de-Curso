<?php
session_start();

function verificarLogin()
{
    // Verifica se a variável de sessão 'usuario_id' está definida. Caso contrário, significa que o usuário não está logado
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: /sistema/views/login.php");
    
        exit();
    }
}
?>

