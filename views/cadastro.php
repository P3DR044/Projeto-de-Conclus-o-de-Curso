<?php
session_start();
include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);  // Criptografa a senha

    // Chama o método para criar o usuário, passando os dados coletados
    $resultado = $usuario->criarUsuario($nome, $email, $senha);

    if ($resultado === true) {
        // Caso sucesso, exibe mensagem de sucesso e redireciona após 2 segundos
        $mensagem = "Cadastro realizado com sucesso! Você será redirecionado para a página de login.";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';  // Redireciona para o login
                }, 2000);  // Redireciona após 2 segundos
              </script>";
    } elseif ($resultado === "email_existente") {
        $mensagem = "Erro: E-mail já está em uso. Tente outro e-mail.";
    } else {
        $mensagem = "Erro ao cadastrar usuário. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="form-container">
        <div class="logo">
            <i class="fa fa-user"><img src="../images/avatar-login.png" alt="" style="width: 145px; height: 145px;"></i>
        </div>
        <h2>Cadastro de Usuário</h2>

        <?php if (!empty($mensagem)) : ?>
            <div class="<?= $resultado === true ? 'success-message' : 'error-message' ?>" style="margin-bottom: 10px;">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit">Cadastrar</button>
        </form>

        <p>Já possui cadastro? <a href="login.php">Fazer Login</a></p>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>