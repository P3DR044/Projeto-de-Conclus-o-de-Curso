<?php
include_once('../config/database.php');

function gerarSlug($str) {
    // Transforma em minúsculas
    $str = mb_strtolower($str, 'UTF-8');

    // Remove acentos
    $str = preg_replace('/[áàâãäå]/u', 'a', $str);
    $str = preg_replace('/[éèêë]/u', 'e', $str);
    $str = preg_replace('/[íìîï]/u', 'i', $str);
    $str = preg_replace('/[óòôõö]/u', 'o', $str);
    $str = preg_replace('/[úùûü]/u', 'u', $str);
    $str = preg_replace('/[ç]/u', 'c', $str);

    // Remove tudo que não for letra, número ou espaço
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);

    // Substitui espaços e hífens múltiplos por hífen simples
    $str = preg_replace('/[\s-]+/', '-', $str);

    // Remove hífens extras no começo e no fim
    $str = trim($str, '-');

    return $str;
}


$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, titulo FROM projetos";
$stmt = $db->prepare($query);
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($projetos as $projeto) {
    $slug = gerarSlug($projeto['titulo']);
    $update = $db->prepare("UPDATE projetos SET slug = :slug WHERE id = :id");
    $update->bindParam(':slug', $slug);
    $update->bindParam(':id', $projeto['id']);
    $update->execute();
}

echo "Slugs atualizados!";
