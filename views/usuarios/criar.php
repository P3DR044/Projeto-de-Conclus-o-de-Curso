<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <title>Criar Usuário</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

    <h2>Criar Usuário</h2>

    <form method="POST" action="../../controllers/UsuarioController.php">
        <input type="text" name="nome" placeholder="Nome" required>

        <input type="email" name="email" placeholder="E-mail" required>

        <input type="password" name="senha" placeholder="Senha" required>


        <button type="submit">Criar</button>
    </form>
</body>

</html>