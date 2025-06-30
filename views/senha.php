<?php
session_start();

include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

$mensagem = "";
$mensagem_cor = "red";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    if ($usuario->verificarEmail($email)) {
        // Aqui você pode gerar um token e enviar um e-mail com um link de redefinição
        $mensagem = "Um e-mail de recuperação foi enviado para " . $email . ".";
        $mensagem_cor = "green"; // Muda a cor para verde

        // Redireciona após 3 segundos
        echo "<meta http-equiv='refresh' content='3;url=login.php'>";
    } else {
        $mensagem = "E-mail não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="form-container">
        <div class="logo">
            <i class="fa fa-user"><img src="../images/avatar-login.png" alt="" style="width: 145px; height: 145px;"></i>
        </div>
        <h2>Atualizar senha!</h2>

        <?php if (!empty($mensagem)) : ?>
            <div class="message" style="color: <?= $mensagem_cor ?>; margin-bottom: 10px;">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="senha.php">
            <input type="email" name="email" placeholder="Digite seu e-mail" required>
            <button type="submit">Enviar</button>
        </form>
        <p><a href="login.php">Voltar para o login.</a></p>
    </div>
</body>

</html>