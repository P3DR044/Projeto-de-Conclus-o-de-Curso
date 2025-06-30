<?php
include_once('../../../config/database.php');

// Extrai o slug da URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    echo "Projeto não especificado.";
    exit();
}

$database = new Database();
$db = $database->getConnection();


// Busca pelo slug
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
        WHERE projetos.slug = :slug
        AND projetos.status = 'aprovado'
        GROUP BY projetos.id";

$stmt = $db->prepare($sql);
$stmt->bindParam(':slug', $slug);
$stmt->execute();
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    echo "Projeto não encontrado.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Visualizar Projeto</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            margin: 0;
            background: #f0f2f5;
            color: #333;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            max-width: 950px;
            margin: 40px auto;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            text-align: center;
            color: #2c3e50;
        }

        h2 {
            margin-top: 30px;
            font-size: 1.4rem;
        }

        .campo {
            background: #f9fafb;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            word-break: break-word;
            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        .campo strong {
            flex: 0 0 180px;
            color: #3a3a3a;
            font-weight: 700;
            font-size: 1rem;
        }

        .campo .valor {
            flex: 1 1 auto;
            font-size: 1rem;
            color: #555;
            word-break: break-word;
            white-space: pre-wrap;
            overflow-wrap: break-word;
        }

        .campo a.valor {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 600;
            word-break: break-word;
        }

        .campo a.valor:hover {
            text-decoration: underline;
        }

        .campo .valor.empty {
            font-style: italic;
            color: #999;
        }

        .btn-voltar {
            display: inline-block;
            margin-top: 30px;
            background-color: #3498db;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .btn-voltar:hover {
            background-color: #2c80b4;
        }

        hr {
            margin: 40px 0;
            border: none;
            border-top: 1px solid #ccc;
        }

        img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }

        a {
            color: #2980b9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .imagem-wrapper {
            text-align: center;
        }

        @media (max-width: 600px) {
            .campo strong {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }

            .campo {
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Detalhes do Projeto</h1>

        <h2><?= htmlspecialchars($projeto['titulo']) ?></h2>
        <div class="imagem-wrapper">
            <img src="../../../uploads/<?= htmlspecialchars($projeto['imagem_projeto']) ?>" alt="Imagem do Projeto">
        </div>
        <div class="campo"><strong>Descrição:</strong> <?= nl2br(htmlspecialchars($projeto['descricao'])) ?></div>
        <div class="campo"><strong>Categoria:</strong> <?= htmlspecialchars($projeto['categoria']) ?></div>
        <div class="campo"><strong>Link do Vídeo:</strong> <a href="<?= htmlspecialchars($projeto['link_video']) ?>" target="_blank"><?= htmlspecialchars($projeto['link_video']) ?></a></div>
        <div class="campo"><strong>Link do Relatório:</strong> <a href="<?= htmlspecialchars($projeto['link_poster']) ?>" target="_blank"><?= htmlspecialchars($projeto['link_poster']) ?></a></div>

        <div class="campo"><strong>Status:</strong> <?= htmlspecialchars($projeto['status']) ?></div>

        <hr>

        <h2>Dados do Participante (Aluno)</h2>
        <div class="campo"><strong>Nome:</strong> <?= htmlspecialchars($projeto['participantes_nome_completo']) ?></div>
        <div class="campo"><strong>Email:</strong> <?= htmlspecialchars($projeto['participantes_email']) ?></div>
        <div class="campo"><strong>Telefone:</strong> <?= htmlspecialchars($projeto['participantes_telefone']) ?></div>
        <div class="campo"><strong>CPF:</strong> <?= htmlspecialchars($projeto['participantes_cpf']) ?></div>
        <div class="campo"><strong>Data de Nascimento:</strong> <?= htmlspecialchars($projeto['participantes_data_nascimento']) ?></div>
        <div class="campo"><strong>Curso:</strong> <?= htmlspecialchars($projeto['participantes_tipo_curso']) ?></div>
        <div class="campo"><strong>Período:</strong> <?= htmlspecialchars($projeto['participantes_serie']) ?></div>

        <hr>

        <h2>Escola / Instituição</h2>
        <div class="campo"><strong>Nome da Escola:</strong> <?= htmlspecialchars($projeto['participantes_instituicao']) ?></div>
        <div class="campo"><strong>Município:</strong> <?= htmlspecialchars($projeto['participantes_municipio']) ?></div>
        <div class="campo"><strong>Estado:</strong> <?= htmlspecialchars($projeto['participantes_estado']) ?></div>
        <div class="campo"><strong>Tipo:</strong> <?= htmlspecialchars($projeto['participantes_dependencia']) ?></div>

        <hr>

        <h2>Professor Orientador</h2>
        <div class="campo"><strong>Nome:</strong> <?= htmlspecialchars($projeto['nome_orientador']) ?></div>
        <div class="campo"><strong>Email:</strong> <?= htmlspecialchars($projeto['email_orientador']) ?></div>
        <div class="campo"><strong>CPF:</strong> <?= htmlspecialchars($projeto['cpf_orientador']) ?></div>

        <hr>

        <h2>Termo de Compromisso</h2>
        <p>Ao inscrever este trabalho, concordamos com o regulamento da FECCIF24. Declaramos que todas as informações contidas neste formulário de inscrição estão corretas, e que o resumo e o pôster estão relacionados apenas ao trabalho desenvolvido nos últimos 12 meses. Estamos cientes de que a não veracidade das informações fornecidas aqui poderá implicar na desclassificação do projeto.
        </p>
        <div class="campo"><strong>Aceite do Termo:</strong> <?= $projeto['participantes_termo'] ? 'Sim' : 'Não' ?></div>

        <a href="../anteriores.php" class="btn-voltar">← Voltar ao Painel</a>
    </div>

</body>

</html>