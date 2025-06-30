<?php
session_start();
include_once('../config/database.php');

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['projeto_id']) || !isset($input['like'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$projeto_id = (int)$input['projeto_id'];
$like = (bool)$input['like'];

$database = new Database();
$conn = $database->getConnection();

try {
    if ($like) {
        // Insere like se não existir
        $stmt = $conn->prepare("INSERT IGNORE INTO projeto_likes (projeto_id, usuario_id) VALUES (:projeto_id, :usuario_id)");
        $stmt->execute([':projeto_id' => $projeto_id, ':usuario_id' => $usuario_id]);
    } else {
        // Remove like
        $stmt = $conn->prepare("DELETE FROM projeto_likes WHERE projeto_id = :projeto_id AND usuario_id = :usuario_id");
        $stmt->execute([':projeto_id' => $projeto_id, ':usuario_id' => $usuario_id]);
    }

    // Contar total de likes atualizados
    $stmt = $conn->prepare("SELECT COUNT(*) FROM projeto_likes WHERE projeto_id = :projeto_id");
    $stmt->execute([':projeto_id' => $projeto_id]);
    $totalLikes = (int)$stmt->fetchColumn();

    echo json_encode(['success' => true, 'totalLikes' => $totalLikes]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados']);
}
