<?php
include_once('../../config/session.php');
include_once('../../config/database.php');
verificarLogin();

$database = new Database();
$conn = $database->getConnection();

// Verifica se o ID do projeto foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do projeto não fornecido.");
}

$id = intval($_GET['id']);

// Verifica se o projeto pertence ao usuário logado
$stmt = $conn->prepare("SELECT * FROM projetos WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute([
    ':id' => $id,
    ':usuario_id' => $_SESSION['usuario_id']
]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    die("Projeto não encontrado ou você não tem permissão.");
}

// Verifica período de submissão
$stmtData = $conn->prepare("SELECT data_inicio, data_fim FROM configuracoes_submissao LIMIT 1");
$stmtData->execute();
$datas = $stmtData->fetch(PDO::FETCH_ASSOC);

$hoje = new DateTime();
$periodo_aberto = false;
if ($datas) {
    $dataInicio = new DateTime($datas['data_inicio']);
    $dataFim = new DateTime($datas['data_fim']);
    if ($hoje >= $dataInicio && $hoje <= $dataFim) {
        $periodo_aberto = true;
    }
}

if (!$periodo_aberto) {
    die("Período de submissão encerrado. Exclusão não permitida.");
}

// Exclui registros relacionados
$stmt1 = $conn->prepare("DELETE FROM projeto_likes WHERE projeto_id = :id");
$stmt1->execute([':id' => $id]);

$stmt2 = $conn->prepare("DELETE FROM participantes WHERE id_projeto = :id");
$stmt2->execute([':id' => $id]);

$stmt3 = $conn->prepare("DELETE FROM orientadores WHERE id_projeto = :id");
$stmt3->execute([':id' => $id]);

// Exclui o projeto
$stmt4 = $conn->prepare("DELETE FROM projetos WHERE id = :id");
if ($stmt4->execute([':id' => $id])) {
    header("Location: listar.php?msg=Projeto excluído com sucesso");
    exit();
} else {
    echo "Erro ao excluir projeto.";
}
