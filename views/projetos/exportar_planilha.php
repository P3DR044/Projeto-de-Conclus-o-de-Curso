<?php
include_once('../../config/session.php');
include_once('../../config/database.php');

if ($_SESSION['tipo'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit();
}


// Definir cabeçalho para download de Excel
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=projetos_submetidos.xls");
header("Pragma: no-cache");
header("Expires: 0");

$database = new Database();
$conn = $database->getConnection();

// Consulta com junções
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
            (SELECT nome_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS nome_orientador,
            (SELECT cpf_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS cpf_orientador,
            (SELECT email_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS email_orientador,
            (SELECT instituicao_orientador FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS instituicao_orientador,
            (SELECT co_nome FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS co_nome,
            (SELECT co_cpf FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS co_cpf,
            (SELECT co_email FROM orientadores WHERE orientadores.id_projeto = projetos.id LIMIT 1) AS co_email
        FROM projetos
        LEFT JOIN participantes ON projetos.id = participantes.id_projeto
        GROUP BY projetos.id
        ORDER BY projetos.data_submissao DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cabeçalho da tabela
echo "\xEF\xBB\xBF";
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Título</th>
        <th>Categoria</th>
        <th>Resumo</th>
        <th>Link Poster</th>
        <th>Link Vídeo</th>
        <th>Descrição</th>
        <th>Projeto Integrador</th>
        <th>Participantes</th>
        <th>CPFs</th>
        <th>Emails</th>
        <th>Telefones</th>
        <th>Nascimentos</th>
        <th>Tipos Curso</th>
        <th>Séries</th>
        <th>Instituições</th>
        <th>Municípios</th>
        <th>Estados</th>
        <th>Dependências</th>
        <th>Termos</th>
        <th>Orientador</th>
        <th>CPF Orientador</th>
        <th>Email Orientador</th>
        <th>Instituição Orientador</th>
        <th>Coorientador</th>
        <th>CPF Coorientador</th>
        <th>Email Coorientador</th>
      </tr>";

// Linhas de dados
foreach ($projetos as $row) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['titulo']}</td>";
    echo "<td>{$row['categoria']}</td>";
    echo "<td>{$row['resumo_arquivo']}</td>";
    echo "<td>{$row['link_poster']}</td>";
    echo "<td>{$row['link_video']}</td>";
    echo "<td>{$row['descricao']}</td>";
    echo "<td>{$row['projeto_integrador']}</td>";
    echo "<td>{$row['participantes_nome_completo']}</td>";
    echo "<td>{$row['participantes_cpf']}</td>";
    echo "<td>{$row['participantes_email']}</td>";
    echo "<td>{$row['participantes_telefone']}</td>";
    echo "<td>{$row['participantes_data_nascimento']}</td>";
    echo "<td>{$row['participantes_tipo_curso']}</td>";
    echo "<td>{$row['participantes_serie']}</td>";
    echo "<td>{$row['participantes_instituicao']}</td>";
    echo "<td>{$row['participantes_municipio']}</td>";
    echo "<td>{$row['participantes_estado']}</td>";
    echo "<td>{$row['participantes_dependencia']}</td>";
    echo "<td>{$row['participantes_termo']}</td>";
    echo "<td>{$row['nome_orientador']}</td>";
    echo "<td>{$row['cpf_orientador']}</td>";
    echo "<td>{$row['email_orientador']}</td>";
    echo "<td>{$row['instituicao_orientador']}</td>";
    echo "<td>{$row['co_nome']}</td>";
    echo "<td>{$row['co_cpf']}</td>";
    echo "<td>{$row['co_email']}</td>";
    echo "</tr>";
}

echo "</table>";

exit;
