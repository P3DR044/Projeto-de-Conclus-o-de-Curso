
<?php
include_once('../../config/session.php');
include_once('../../config/database.php');

verificarLogin();

if ($_SESSION['tipo'] !== 'avaliador') {
    header('Location: ../home.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: avaliador_painel.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();


$projeto_id = isset($_POST['id_projeto']) ? (int)$_POST['id_projeto'] : 0;
$avaliador_id = $_SESSION['usuario_id'];

$camposInt = [
    'criatividade',
    'metodo_cientifico',
    'profundidade',
    'aplicabilidade',
    'apresentacao',
    'carater_integrador'
];

$valores = [];
foreach ($camposInt as $campo) {
    if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
        die("Campo $campo é obrigatório.");
    }
    $nota = (int)$_POST[$campo];
    if ($campo === 'carater_integrador') {
        if ($nota < 1 || $nota > 5) die('Nota fora do intervalo.');
    } else {
        if ($nota < 0 || $nota > 5) die('Nota fora do intervalo.');
    }
    $valores[$campo] = $nota;
}

$observacoes = trim($_POST['observacoes'] ?? '');

$chk = $db->prepare(
    'SELECT 1
     FROM avaliador_areas aa
     JOIN projetos p ON p.categoria = aa.categoria
     WHERE aa.avaliador_id = :av AND p.id = :pr LIMIT 1'
);
$chk->execute([':av' => $avaliador_id, ':pr' => $projeto_id]);
if (!$chk->fetchColumn()) {
    die('Você não tem permissão para avaliar este projeto.');
}

try {
    $sql = "INSERT INTO avaliacoes
            (projeto_id, avaliador_id,
             criatividade, metodo_cientifico, profundidade,
             aplicabilidade, apresentacao, carater_integrador, observacoes)
            VALUES
            (:projeto_id, :avaliador_id,
             :criatividade, :metodo_cientifico, :profundidade,
             :aplicabilidade, :apresentacao, :carater_integrador, :observacoes)
            ON DUPLICATE KEY UPDATE
             criatividade        = VALUES(criatividade),
             metodo_cientifico   = VALUES(metodo_cientifico),
             profundidade        = VALUES(profundidade),
             aplicabilidade      = VALUES(aplicabilidade),
             apresentacao        = VALUES(apresentacao),
             carater_integrador  = VALUES(carater_integrador),
             observacoes         = VALUES(observacoes),
             data_avaliacao      = CURRENT_TIMESTAMP";

    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge(
        [
            ':projeto_id'      => $projeto_id,
            ':avaliador_id'    => $avaliador_id,
            ':observacoes'     => $observacoes
        ],
        array_combine(
            array_map(fn($c) => ":$c", $camposInt),
            array_values($valores)
        )
    ));

    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Avaliação Registrada</title>
    <style>
        .message {
            padding: 20px;
            margin: 50px auto;
            width: 80%;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 18px;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = 'avaliador_painel.php';
        }, 3000); // redireciona após 3 segundos
    </script>
</head>
<body>
    <div class='message'>Avaliação registrada com sucesso! Redirecionando...</div>
</body>
</html>";
    exit();
} catch (PDOException $e) {
    error_log("Erro ao registrar avaliação: " . $e->getMessage());
    $_SESSION['message'] = 'Erro ao registrar avaliação.';
    $_SESSION['message_type'] = 'danger';
    header("Location: avaliador_painel.php");
    exit();
}
