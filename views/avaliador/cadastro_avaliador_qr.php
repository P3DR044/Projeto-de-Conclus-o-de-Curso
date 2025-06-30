<?php
include_once('../../config/database.php');

// Verifica se o token foi passado
if (!isset($_GET['token'])) {
    die("Acesso negado. Token ausente.");
}

$token = $_GET['token'];

// Verifica se o token existe
$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("SELECT * FROM tokens_acesso WHERE token = :token");
$stmt->execute([':token' => $token]);
$tokenValido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tokenValido) {
    die("Acesso negado. Token inválido.");
}

// Buscar todas as categorias e contar projetos pendentes sem avaliador para cada uma
$stmtCat = $conn->query("
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

$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = 'avaliador';

    // 1. Inserir avaliador
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)");
    $stmt->execute([
        ':nome' => $nome,
        ':email' => $email,
        ':senha' => $senha,
        ':tipo' => $tipo
    ]);

    $avaliador_id = $conn->lastInsertId();

    // 2. Inserir preferências e vincular projetos
    if (isset($_POST['areas']) && is_array($_POST['areas'])) {
        $stmtArea = $conn->prepare("INSERT INTO avaliador_areas (avaliador_id, categoria, quantidade) VALUES (:avaliador_id, :categoria, :quantidade)");

        $stmtProjetos = $conn->prepare("
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

        $insertProjetoAvaliador = $conn->prepare("INSERT INTO projeto_avaliador (projeto_id, avaliador_id) VALUES (:projeto_id, :avaliador_id)");

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

    $msg = "Cadastro realizado com sucesso! Seus projetos foram atribuídos automaticamente.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Avaliador</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- FontAwesome -->
    <style>
        /* Estilos para o corpo da página */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
            color: black;
            background-color: #f5f5f5;
        }

        /* Barra lateral (navbar) */
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

        /* Estilos para o formulário */
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

        th,
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        input[type="number"] {
            width: 60px;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <div class="spot">
        <ul>
            <li><a href="../../logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Cadastrar Avaliador</h1>

        <?php if ($msg): ?>
            <p class="msg-success"><?= htmlspecialchars($msg) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Nome completo:</label>
            <input type="text" name="nome" required />

            <label>Email:</label>
            <input type="email" name="email" required />

            <label>Senha:</label>
            <input type="password" name="senha" required />

            <h3>Áreas que você deseja avaliar:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Área</th>
                        <th>Projetos pendentes disponíveis</th>
                        <th>Quantidade de projetos que você deseja avaliar</th>
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

            <button type="submit">Finalizar Cadastro</button>
        </form>
    </div>
</body>

</html>