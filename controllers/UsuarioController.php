<?php
include_once '../models/Usuario.php';
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'criar') {
        $nome = $_POST['nome']; 
        $email = $_POST['email']; 
        $senha = $_POST['senha']; 

        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

        $usuario = new Usuario($db);

        $usuarioCriado = $usuario->criarUsuario($nome, $email, $senha_hash, $tipo_usuario, $data_nascimento, $estado);

        if ($usuarioCriado) {
            header("Location: ../views/login.php");
            exit(); 
        } else {
            $erro = "Erro ao criar usuário. Tente novamente.";
        }
    }
}
?>
