<?php
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID do projeto não fornecido']);
    exit;
}

$idProjeto = $_GET['id'];

include_once '../config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();

    $sql = "SELECT 
                projetos.*, 
                GROUP_CONCAT(participantes.nome_completo) AS participantes_nome_completo,
                GROUP_CONCAT(participantes.cpf) AS participantes_cpf,
                GROUP_CONCAT(participantes.email) AS participantes_email,
                GROUP_CONCAT(participantes.telefone) AS participantes_telefone,
                GROUP_CONCAT(participantes.data_nascimento) AS participantes_data_nascimento,
                GROUP_CONCAT(participantes.tipo_curso) AS participantes_tipo_curso,
                GROUP_CONCAT(participantes.serie) AS participantes_serie,
                GROUP_CONCAT(participantes.instituicao) AS participantes_instituicao,
                GROUP_CONCAT(participantes.municipio) AS participantes_municipio,
                GROUP_CONCAT(participantes.estado) AS participantes_estado,
                GROUP_CONCAT(participantes.dependencia) AS participantes_dependencia,
                GROUP_CONCAT(participantes.termo) AS participantes_termo,
                GROUP_CONCAT(participantes.nome_social) AS participantes_nome_social,
                GROUP_CONCAT(participantes.identidade_genero) AS participantes_identidade_genero,
                GROUP_CONCAT(participantes.sexo) AS participantes_sexo,
                GROUP_CONCAT(participantes.etnia) AS participantes_etnia,
                GROUP_CONCAT(participantes.cidade) AS participantes_cidade,
                (SELECT nome_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS nome_orientador,
                (SELECT cpf_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS cpf_orientador,
                (SELECT email_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS email_orientador,
                (SELECT instituicao_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS instituicao_orientador,
                (SELECT co_nome FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS co_nome,
                (SELECT co_cpf FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS co_cpf,
                (SELECT co_email FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS co_email
            FROM projetos
            LEFT JOIN participantes ON projetos.id = participantes.id_projeto
            WHERE projetos.id = ?
            GROUP BY projetos.id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$idProjeto]);

    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        echo json_encode(['error' => 'Projeto não encontrado']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao buscar projeto: ' . $e->getMessage()]);
}
