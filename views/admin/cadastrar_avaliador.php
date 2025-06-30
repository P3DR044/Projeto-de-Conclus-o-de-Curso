<?php
include_once('../../config/session.php');
include_once('../../config/database.php');

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: ../../../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Buscar todas as categorias e contar projetos com menos de 3 avaliadores
$stmtCat = $db->query("
    SELECT 
        c.categoria,
        COALESCE(p.total_projetos, 0) AS total_projetos
    FROM (
        SELECT DISTINCT categoria FROM projetos WHERE categoria IS NOT NULL AND categoria != ''
    ) AS c
    LEFT JOIN (
    SELECT p.categoria, COUNT(*) AS total_projetos
    FROM projetos p
    LEFT JOIN (
        SELECT projeto_id, COUNT(*) AS total_avaliadores
        FROM projeto_avaliador
        GROUP BY projeto_id
    ) pa ON p.id = pa.projeto_id
    WHERE p.status = 'aprovado'
    AND (pa.total_avaliadores IS NULL OR pa.total_avaliadores < 3)
    GROUP BY p.categoria
) AS p ON c.categoria = p.categoria
    ORDER BY c.categoria
");
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = 'avaliador';

    // Inserir avaliador
    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senha,
        ':tipo' => $tipo
    ]);

    $avaliador_id = $db->lastInsertId();

    // Inserir preferências e vincular projetos
    if (isset($_POST['areas']) && is_array($_POST['areas'])) {
        $stmtArea = $db->prepare("INSERT INTO avaliador_areas (avaliador_id, categoria, quantidade) VALUES (:avaliador_id, :categoria, :quantidade)");

        $stmtProjetos = $db->prepare("
            SELECT p.id
            FROM projetos p
            LEFT JOIN (
                SELECT projeto_id, COUNT(*) AS total_avaliadores
                FROM projeto_avaliador
                GROUP BY projeto_id
            ) pa ON p.id = pa.projeto_id
            WHERE p.status = 'aprovado' AND p.categoria = :categoria
            AND (pa.total_avaliadores IS NULL OR pa.total_avaliadores < 3)
            LIMIT :qtd
        ");

        $insertProjetoAvaliador = $db->prepare("INSERT INTO projeto_avaliador (projeto_id, avaliador_id) VALUES (:projeto_id, :avaliador_id)");

        foreach ($_POST['areas'] as $categoria => $quantidade) {
            $qtd = (int)$quantidade;
            if ($qtd > 0) {
                // Salvar preferência
                $stmtArea->execute([
                    ':avaliador_id' => $avaliador_id,
                    ':categoria' => $categoria,
                    ':quantidade' => $qtd
                ]);

                // Buscar projetos
                $stmtProjetos->bindValue(':categoria', $categoria, PDO::PARAM_STR);
                $stmtProjetos->bindValue(':qtd', $qtd, PDO::PARAM_INT);
                $stmtProjetos->execute();
                $projetos = $stmtProjetos->fetchAll(PDO::FETCH_ASSOC);

                // Atribuir projetos
                foreach ($projetos as $proj) {
                    $insertProjetoAvaliador->execute([
                        ':projeto_id' => $proj['id'],
                        ':avaliador_id' => $avaliador_id
                    ]);
                }
            }
        }
    }

    $msg = "Avaliador cadastrado com sucesso e projetos atribuídos!";
}
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cadastrar Avaliador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
            color: black;
            background-color: #f5f5f5;
        }

        .spot {
            width: 250px;
            height: 100vh;
            background-color: #CD853F;
            color: black;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            border-right: 2px solid #ccc;
        }

        .spot ul {
            margin: 0;
            padding: 10px;
            list-style: none;
        }

        .spot li {
            list-style-type: none;
            padding: 20px 15px;
            border-bottom: 1px solid #f4f4f4;
        }

        .spot li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 17px;
        }

        .spot li a i {
            margin-right: 10px;
            font-size: 18px;
        }

        .spot li a:hover {
            transform: scale(1.1);
            background-color: black;
            border-radius: 5px;
            padding: 10px 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        h2 {
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            width: 400px;
            margin: 20px auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 95%;
            padding: 8px;
            margin-bottom: 20px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .msg-success {
            color: green;
            font-weight: bold;
            margin-top: 10px;
        }

        #closeButton {
            color: white;
            transform: scale(1.1);
            background-color: #8B0000;
            border-radius: 5px;
            padding: 10px 10px;
            border-bottom: none !important;
        }

        .spot li:last-child {
            border-bottom: none !important;
        }

        #closeButton:hover {
            background-color: black;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        input[type="number"] {
            width: 60px;
        }
    </style>
</head>

<body>
    <div class="spot">
        <ul>
            <li><a href="../home.php"><i class="fa fa-home"></i>Página Inicial</a></li>
            <li><a href="../avaliador/avaliador_painel.php"><i class="fa fa-user"></i>Painel do Avaliador</a></li>
            <li><a href="../projetos/exportar_planilha.php"><i class="fa fa-file-excel"></i>Exportar Planilha dos Projetos</a></li>
            <li><a href="../admin/tools/qr_avaliador.php"><i class="fa fa-qrcode"></i>QR Code para o cadastro dos Avaliadores</a></li>
            <li><a href="definir_intervalo.php"><i class="fa fa-calendar"></i>Definir período limite</a></li>
            <li><a href="../../logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Cadastrar Avaliador</h1>

        <?php if (isset($msg)): ?>
            <p class="msg-success"><?= htmlspecialchars($msg) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Nome completo:</label>
            <input type="text" name="nome" required />

            <label>Email:</label>
            <input type="email" name="email" required />

            <label>Senha:</label>
            <input type="password" name="senha" required />

            <h3>Áreas que deseja avaliar:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Área</th>
                        <th>Projetos pendentes disponíveis</th>
                        <th>Quantidade de projetos a avaliar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['categoria']) ?></td>
                            <td><?= $cat['total_projetos'] ?></td>
                            <td>
                                <input type="number" name="areas[<?= htmlspecialchars($cat['categoria']) ?>]" min="0" value="0" max="<?= $cat['total_projetos'] ?>" />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit">Cadastrar Avaliador</button>
        </form>
    </div>
</body>

</html>