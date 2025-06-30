<?php
session_start();

include_once '../config/database.php';
include_once '../models/Usuario.php';

// Cria uma nova instância da conexão com o banco e da classe Usuario
$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Chama o método de login da classe Usuario, passando os dados coletados
    $user = $usuario->login($email, $senha);

    // Se o login for bem-sucedido, cria variáveis de sessão e redireciona o usuário
    if ($user) {
        $_SESSION['tipo'] = $user['tipo']; // 
        $_SESSION['usuario_id'] = $user['id'];  // Armazena o ID do usuário na sessão
        $_SESSION['nome_usuario'] = $user['nome'];  // Armazena o nome do usuário na sessão

        switch ($_SESSION['tipo']) {
            case 'admin':
                header('Location: ../views/admin/cadastrar_avaliador.php');
                break;
            case 'avaliador':
                header('Location: ../views/avaliador/avaliador_painel.php');
                break;
            default:
                header("Location: ../views/home.php");
        }
        exit();
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="form-container">
        <div class="logo">
            <i class="fa fa-user"><img src="../images/avatar-login.png" alt="" style="width: 145px; height: 145px;"></i>
        </div>
        <h2>Bem-vindo de volta!</h2>

        <?php if (!empty($erro)) : ?>
            <div class="error-message" style="color: red; margin-bottom: 10px;">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <p class="senha"><a href="senha.php">Esqueceu a senha?</a></p>
            <button type="submit">Entrar</button>
        </form>
        <p>Não tem cadastro? <a href="cadastro.php">Crie agora sua conta.</a></p>
    </div>
</body>

</html>