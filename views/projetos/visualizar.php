<?php
include_once('../../config/session.php');

verificarLogin();


$servername = "localhost";
$username = "u915057572_Admin";
$password = "F3cc1f2022";
$dbname = "u915057572_projeto_tcc";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = $_GET['id']; // Obtém o ID do projeto passado na URL

    // Prepara uma consulta SQL para buscar os dados do projeto baseado no ID fornecido
    $sql = "SELECT * FROM projetos WHERE id = ?"; // Usa uma consulta parametrizada para evitar SQL Injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se algum projeto foi encontrado com o ID fornecido
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
    } else {

        echo "Projeto não encontrado.";
        exit;
    }
} else {

    echo "ID do projeto não informado.";
    exit;
}

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vizualizar Projeto</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            background-color: white;
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
            color: black;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 16px;
        }

        .spot li a i {
            margin-right: 10px;
            font-size: 18px;
        }

        .spot li a:hover {
            background-color: lightgray;
            border-radius: 5px;
        }

        .spot li ul {
            display: none;
            padding-left: 20px;
        }

        .spot li:hover ul {
            display: block;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
        }

        .main-content h1 {
            font-size: 24px;
            font-weight: bold;
            color: #333333;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .main-content form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }

        .main-content form label {
            font-size: 14px;
            font-weight: bold;
            color: #555555;
            margin-bottom: 5px;
        }

        .main-content form input {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .main-content form input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0px 0px 5px rgba(0, 123, 255, 0.5);
        }

        .main-content form button {
            padding: 12px 20px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .main-content form button:hover {
            background-color: #0056b3;
        }

        .main-content a button {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            background-color: #6c757d;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .main-content a button:hover {
            background-color: #5a6268;
        }

        .success-message {
            background-color: #28a745;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .main-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .main-content table th,
        .main-content table td {
            padding: 12px;
            text-align: left;
            font-size: 14px;
            color: #555555;
            border-bottom: 1px solid #ddd;
        }

        .main-content table th {
            background-color: #f1f1f1;
            font-weight: bold;
            color: #333333;
        }

        .main-content table tbody tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }

        .main-content table tbody td a button {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .main-content table tbody td a button:hover {
            background-color: #0056b3;
        }

        .main-content table tbody td a button:active {
            background-color: #003d80;
        }

        .main-content .info-block {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .main-content .info-block div {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0px 1px 4px rgba(0, 0, 0, 0.1);
        }

        .main-content .info-block div span {
            font-size: 14px;
            font-weight: bold;
            color: #555555;
        }

        #closeButton {
            color: #e74c3c;
        }

        #closeButton:hover {
            color: #c0392b;
        }
    </style>
</head>

<body>

    <div class="spot">
        <ul>
            <li><a href="../home.php"><i class="fa fa-home"></i>Página Inicial</a></li>

            <li><a href="criar.php"><i class="fa fa-upload"></i>Submeter um projeto</a></li>

            <li><a href="anteriores.php"><i class="fa fa-folder-open"></i>Projetos de anos anteriores</a></li>

            <li><a href="#"><i class="fa fa-list-alt"></i>Projetos submetidos</a></li>

            <li><a href="#"><i class="fa fa-check"></i>Projetos avaliados</a></li>

            <li><a href="/Projeto_TCC/logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Visualizar Projeto</h1>
        <a href="listar.php"><button>Voltar</button></a>

        <table>
            <tr>
                <th>Título do projeto:</th>
                <td><?php echo htmlspecialchars($project['titulo']); ?></td>
            </tr>
            <tr>
                <th>Participação no projeto:</th>
                <td><?php echo htmlspecialchars($project['participacao']); ?></td>
            </tr>
            <tr>
                <th>Número de estudantes que realizaram o projeto:</th>
                <td><?php echo htmlspecialchars($project['numero_estudantes']); ?></td>
            </tr>
            <tr>
                <th>Estado da Instituição:</th>
                <td><?php echo htmlspecialchars($project['estado_instituicao']); ?></td>
            </tr>
            <tr>
                <th>Cidade da Instituição:</th>
                <td><?php echo htmlspecialchars($project['cidade_instituicao']); ?></td>
            </tr>
            <tr>
                <th>Nome da Instituição:</th>
                <td><?php echo nl2br(htmlspecialchars($project['nome_instituicao'])); ?></td>
            </tr>
            <tr>
                <th>Nome do Orientador do projeto:</th>
                <td><?php echo nl2br(htmlspecialchars($project['orientador'])); ?></td>
            </tr>
            <tr>
                <th>Nome do Coorientador do projeto:</th>
                <td><?php echo nl2br(htmlspecialchars($project['coorientador'])); ?></td>
            </tr>
            <tr>
                <th>Categoria do projeto:</th>
                <td><?php echo nl2br(htmlspecialchars($project['categoria_projeto'])); ?></td>
            </tr>
            <tr>
                <th>Área do projeto:</th>
                <td><?php echo nl2br(htmlspecialchars($project['area'])); ?></td>
            </tr>
            <tr>
                <th>Data da submissão do projeto:</th>
                <td><?php echo nl2br(htmlspecialchars($project['data_submissao'])); ?></td>
            </tr>
        </table>

    </div>

</body>


</html>

<?php
$conn->close();
?>