<?php

require_once('../../../libs/qrlib.php');
require_once('../../../config/database.php');

while (ob_get_level()) {
    ob_end_clean();
}


$token = bin2hex(random_bytes(32));
$link = 'http://localhost/Projeto_TCC/views/avaliador/cadastro_avaliador_qr.php?token=' . $token;


$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("INSERT INTO tokens_acesso (token, tipo, criado_em) VALUES (:token, 'avaliador', NOW())");
$stmt->execute([':token' => $token]);

header('Content-Type: image/png');

QRcode::png($link);
exit;
