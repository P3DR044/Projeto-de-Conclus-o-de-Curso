<?php
include_once('../../config/session.php');
include_once('../../config/database.php');

verificarLogin();

$database = new Database();
$conn = $database->getConnection();

// Verifica se o ID do projeto foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Projeto não especificado.");
}

$id_projeto = $_GET['id'];

// Verifica se o projeto pertence ao usuário logado
$stmt = $conn->prepare("SELECT * FROM projetos WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute([
    ':id' => $id_projeto,
    ':usuario_id' => $_SESSION['usuario_id']
]);
$projeto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$projeto) {
    die("Projeto não encontrado ou você não tem permissão.");
}

// Busca dados relacionados (participantes, orientadores)
$stmtPart = $conn->prepare("SELECT * FROM participantes WHERE id_projeto = :id_projeto ORDER BY id ASC");
$stmtPart->execute([':id_projeto' => $id_projeto]);
$participantes = $stmtPart->fetchAll(PDO::FETCH_ASSOC);

$stmtOri = $conn->prepare("SELECT * FROM orientadores WHERE id_projeto = :id_projeto");
$stmtOri->execute([':id_projeto' => $id_projeto]);
$orientadores = $stmtOri->fetch(PDO::FETCH_ASSOC);

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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $periodo_aberto) {

    $stmt = $conn->prepare("UPDATE projetos SET 
        titulo = :titulo, 
        categoria = :categoria, 
        projeto_integrador = :projeto_integrador, 
        link_poster = :link_poster, 
        link_video = :link_video, 
        resumo_texto = :resumo_texto
        WHERE id = :id");

    $stmt->execute([
        ':titulo' => $_POST['titulo'] ?? '',
        ':categoria' => $_POST['categoria'] ?? '',
        ':projeto_integrador' => $_POST['projeto_integrador'] ?? '',
        ':resumo_texto' => $_POST['resumo_texto'] ?? '',
        ':link_poster' => $_POST['link_poster'] ?? '',
        ':link_video' => $_POST['link_video'] ?? '',
        ':id' => $id_projeto
    ]);

    // Atualização dos participantes
    $stmtUpdatePart = $conn->prepare("UPDATE participantes SET 
        nome_completo = :nome_completo,
        nome_social = :nome_social,
        cpf = :cpf,
        email = :email,
        telefone = :telefone,
        data_nascimento = :data_nascimento,
        tipo_curso = :tipo_curso,
        serie = :serie,
        instituicao = :instituicao,
        municipio = :municipio,
        estado = :estado,
        cidade = :cidade,
        dependencia = :dependencia,
        sexo = :sexo,
        identidade_genero = :identidade_genero,
        etnia = :etnia   
        WHERE id = :id");

    $dataNascimento = $_POST['data_nascimento'] ?? null;
    if (empty($dataNascimento)) {
        $dataNascimento = null;
    }

    for ($i = 0; $i < count($participantes); $i++) {
        $stmtUpdatePart->execute([
            ':nome_completo' => $_POST['nome_completo'][$i] ?? '',
            ':nome_social' => $_POST['nome_social'][$i] ?? '',
            ':cpf' => $_POST['cpf'][$i] ?? '',
            ':email' => $_POST['email'][$i] ?? '',
            ':telefone' => $_POST['telefone'][$i] ?? '',
            ':data_nascimento' => $_POST['data_nascimento'][$i] ?? null,
            ':tipo_curso' => $_POST['tipo_curso'][$i] ?? '',
            ':serie' => $_POST['serie'][$i] ?? '',
            ':instituicao' => $_POST['instituicao'][$i] ?? '',
            ':municipio' => $_POST['municipio'][$i] ?? '',
            ':estado' => $_POST['estado'][$i] ?? '',
            ':cidade' => $_POST['cidade'][$i] ?? '',
            ':dependencia' => $_POST['dependencia'][$i] ?? '',
            ':sexo' => $_POST['sexo'][$i] ?? '',
            ':identidade_genero' => $_POST['identidade_genero'][$i] ?? '',
            ':etnia' => $_POST['etnia'][$i] ?? '',
            ':id' => $participantes[$i]['id']
        ]);
    }

    $updateProjeto = $conn->prepare("
    UPDATE orientadores SET
        nome_orientador = :nome_orientador,
        email_orientador = :email_orientador,
        instituicao_orientador = :instituicao_orientador,
        co_nome = :co_nome,
        co_cpf = :co_cpf,
        co_email = :co_email
    WHERE id = :id
");


    $nome_orientador = $_POST['nome_orientador'] ?? '';
    $email_orientador = $_POST['email_orientador'] ?? '';
    $instituicao_orientador = $_POST['instituicao_orientador'] ?? '';
    $co_nome = $_POST['co_nome'] ?? '';
    $co_cpf = $_POST['co_cpf'] ?? '';
    $co_email = $_POST['co_email'] ?? '';

    $updateProjeto->execute([
        ':nome_orientador' => $nome_orientador,
        ':email_orientador' => $email_orientador,
        ':instituicao_orientador' => $instituicao_orientador,
        ':co_nome' => $co_nome,
        ':co_cpf' => $co_cpf,
        ':co_email' => $co_email,
        ':id' => $orientadores['id']
    ]);




    $_SESSION['message'] = "Projeto atualizado com sucesso!";
    $_SESSION['message_type'] = "success";
    header("Location: listar.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Projeto</title>
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

        .main-content {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.08);
            font-family: Arial, sans-serif;
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-section {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            margin-top: 15px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fff;
            transition: border 0.3s;
        }

        input:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        button,
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover,
        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .info-message {
            background-color: #ffe5e5;
            padding: 10px;
            border: 1px solid red;
            color: red;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>

    </style>
</head>

<body>
    <div class="spot">
        <ul>
            <li><a href="../home.php"><i class="fa fa-home"></i>Página Inicial</a></li>
            <li><a href="anteriores.php"><i class="fa fa-folder-open"></i>Projetos separados por categoria</a></li>
            <li><a href="criar.php"><i class="fa fa-upload"></i>Submeter um projeto</a></li>
            <li><a href="listar.php"><i class="fa fa-list-alt"></i>Projetos submetidos</a></li>
            <li><a href="avaliados.php"><i class="fa fa-check"></i>Projetos avaliados</a></li>
            <li><a href="/sistema/logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content" style="padding-left: 20px; padding-right: 20px;">

        <h2>Editar Projeto</h2>

        <?php if (!$periodo_aberto): ?>
            <p style="color:red;">Período de submissão encerrado.</p>
        <?php else: ?>
            <form method="post" enctype="multipart/form-data">
                <?php
                $quantidade = count($participantes);
                $quantidade_maxima = 5;

                for ($i = 0; $i < max($quantidade, 1); $i++):
                ?>
                    <h2>Etapa <?= $i + 1 ?>: Dados do Integrante <?= $i + 1 ?></h2>


                    <label>Nome completo:</label>
                    <input type="text" name="nome_completo[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['nome_completo'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>

                    <label>Nome Social:</label>
                    <input type="text" name="nome_social[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['nome_social'] ?? '') ?>"><br>

                    <label>CPF:</label>
                    <input type="text" name="cpf[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['cpf'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>

                    <label>Email:</label>
                    <input type="email" name="email[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['email'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>

                    <label>Telefone (Whatsapp):</label>
                    <input type="text" name="telefone[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['telefone'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>

                    <label>Data de nascimento:</label>
                    <input type="date" name="data_nascimento[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['data_nascimento'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>


                    <label for="sexo_<?= $i ?>">Sexo:</label>
                    <select id="sexo_<?= $i ?>" name="sexo[<?= $i ?>]" required>
                        <option value="">Selecione</option>
                        <option value="Masculino" <?= (isset($participantes[$i]['sexo']) && $participantes[$i]['sexo'] === 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                        <option value="Feminino" <?= (isset($participantes[$i]['sexo']) && $participantes[$i]['sexo'] === 'Feminino') ? 'selected' : '' ?>>Feminino</option>
                        <option value="Intersexo" <?= (isset($participantes[$i]['sexo']) && $participantes[$i]['sexo'] === 'Intersexo') ? 'selected' : '' ?>>Intersexo</option>
                    </select><br>


                    <label for="identidade_genero_<?= $i ?>">Identidade de Gênero:</label>
                    <select id="identidade_genero_<?= $i ?>" name="identidade_genero[<?= $i ?>]" required>
                        <option value="">Selecione</option>
                        <option value="Homem cis" <?= (isset($participantes[$i]['identidade_genero']) && $participantes[$i]['identidade_genero'] === 'Homem cis') ? 'selected' : '' ?>>Homem cisgênero</option>
                        <option value="Homem trans" <?= (isset($participantes[$i]['identidade_genero']) && $participantes[$i]['identidade_genero'] === 'Homem trans') ? 'selected' : '' ?>>Homem transgênero</option>
                        <option value="Mulher cis" <?= (isset($participantes[$i]['identidade_genero']) && $participantes[$i]['identidade_genero'] === 'Mulher cis') ? 'selected' : '' ?>>Mulher cisgênero</option>
                        <option value="Mulher trans" <?= (isset($participantes[$i]['identidade_genero']) && $participantes[$i]['identidade_genero'] === 'Mulher trans') ? 'selected' : '' ?>>Mulher transgênero</option>
                        <option value="Não-binário" <?= (isset($participantes[$i]['identidade_genero']) && $participantes[$i]['identidade_genero'] === 'Não-binário') ? 'selected' : '' ?>>Não-binário</option>
                        <option value="Outro" <?= (isset($participantes[$i]['identidade_genero']) && $participantes[$i]['identidade_genero'] === 'Outro') ? 'selected' : '' ?>>Outro</option>
                    </select><br>


                    <label for="etnia_<?= $i ?>">Como você se autodeclara?</label>
                    <select id="etnia_<?= $i ?>" name="etnia[<?= $i ?>]" required>
                        <option value="">Selecione</option>
                        <option value="Branca" <?= (isset($participantes[$i]['etnia']) && $participantes[$i]['etnia'] === 'Branca') ? 'selected' : '' ?>>Branco</option>
                        <option value="Preta" <?= (isset($participantes[$i]['etnia']) && $participantes[$i]['etnia'] === 'Preta') ? 'selected' : '' ?>>Preto</option>
                        <option value="Parda" <?= (isset($participantes[$i]['etnia']) && $participantes[$i]['etnia'] === 'Parda') ? 'selected' : '' ?>>Pardo</option>
                        <option value="Amarela" <?= (isset($participantes[$i]['etnia']) && $participantes[$i]['etnia'] === 'Amarela') ? 'selected' : '' ?>>Amarelo</option>
                        <option value="Indígena" <?= (isset($participantes[$i]['etnia']) && $participantes[$i]['etnia'] === 'Indígena') ? 'selected' : '' ?>>Indígena</option>
                    </select><br>

                    <label for="tipo_curso_<?= $i ?>">Tipo de Curso:</label>
                    <select id="tipo_curso_<?= $i ?>" name="tipo_curso[<?= $i ?>]" <?= $i <= 2 ? 'required' : '' ?>>
                        <option value="">Selecione</option>
                        <option value="Ensino Médio" <?= (isset($participantes[$i]['tipo_curso']) && $participantes[$i]['tipo_curso'] === 'Ensino Médio') ? 'selected' : '' ?>>Ensino Médio</option>
                        <option value="Ensino Técnico Integrado" <?= (isset($participantes[$i]['tipo_curso']) && $participantes[$i]['tipo_curso'] === 'Ensino Técnico Integrado') ? 'selected' : '' ?>>Ensino Técnico Integrado</option>
                        <option value="Ensino Técnico Concomitante/Subsequente" <?= (isset($participantes[$i]['tipo_curso']) && $participantes[$i]['tipo_curso'] === 'Ensino Técnico Concomitante/Subsequente') ? 'selected' : '' ?>>Ensino Técnico Concomitante/Subsequente</option>
                    </select><br>

                    <label for="serie_<?= $i ?>">Ano / série / semestre / ou módulo que está cursando:</label>
                    <select id="serie_<?= $i ?>" name="serie[<?= $i ?>]" <?= $i <= 2 ? 'required' : '' ?>>
                        <option value="">Selecione</option>
                        <option value="1º ano do Médio" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '1º ano do Médio') ? 'selected' : '' ?>>1º ano do Médio</option>
                        <option value="2º ano do Médio" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '2º ano do Médio') ? 'selected' : '' ?>>2º ano do Médio</option>
                        <option value="3º ano do Médio" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '3º ano do Médio') ? 'selected' : '' ?>>3º ano do Médio</option>
                        <option value="4º ano do Médio" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '4º ano do Médio') ? 'selected' : '' ?>>4º ano do Médio</option>
                        <option value="1º semestre" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '1º semestre') ? 'selected' : '' ?>>1º semestre</option>
                        <option value="2º semestre" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '2º semestre') ? 'selected' : '' ?>>2º semestre</option>
                        <option value="3º semestre" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '3º semestre') ? 'selected' : '' ?>>3º semestre</option>
                        <option value="4º semestre" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '4º semestre') ? 'selected' : '' ?>>4º semestre</option>
                        <option value="5º semestre" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '5º semestre') ? 'selected' : '' ?>>5º semestre</option>
                        <option value="6º semestre" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === '6º semestre') ? 'selected' : '' ?>>6º semestre</option>
                        <option value="Concluído" <?= (isset($participantes[$i]['serie']) && $participantes[$i]['serie'] === 'Concluído') ? 'selected' : '' ?>>Concluído</option>
                    </select><br>

                    <label>Instituição de Ensino:</label>
                    <input type="text" name="instituicao[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['instituicao'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>

                    <label>Município da Instituição:</label>
                    <input type="text" name="municipio[<?= $i ?>]" value="<?= htmlspecialchars($participantes[$i]['municipio'] ?? '') ?>" <?= $i <= 2 ? 'required' : '' ?>><br>

                    <label for="estado_<?= $i ?>">Estado da sua Instituição:</label>
                    <select id="estado_<?= $i ?>" name="estado[<?= $i ?>]" <?= $i <= 2 ? 'required' : '' ?>>
                        <option value="">Selecione</option>
                        <option value="AC" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'AC') ? 'selected' : '' ?>>Acre (AC)</option>
                        <option value="AL" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'AL') ? 'selected' : '' ?>>Alagoas (AL)</option>
                        <option value="AP" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'AP') ? 'selected' : '' ?>>Amapá (AP)</option>
                        <option value="AM" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'AM') ? 'selected' : '' ?>>Amazonas (AM)</option>
                        <option value="BA" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'BA') ? 'selected' : '' ?>>Bahia (BA)</option>
                        <option value="CE" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'CE') ? 'selected' : '' ?>>Ceará (CE)</option>
                        <option value="DF" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'DF') ? 'selected' : '' ?>>Distrito Federal (DF)</option>
                        <option value="ES" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'ES') ? 'selected' : '' ?>>Espírito Santo (ES)</option>
                        <option value="GO" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'GO') ? 'selected' : '' ?>>Goiás (GO)</option>
                        <option value="MA" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'MA') ? 'selected' : '' ?>>Maranhão (MA)</option>
                        <option value="MT" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'MT') ? 'selected' : '' ?>>Mato Grosso (MT)</option>
                        <option value="MS" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'MS') ? 'selected' : '' ?>>Mato Grosso do Sul (MS)</option>
                        <option value="MG" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'MG') ? 'selected' : '' ?>>Minas Gerais (MG)</option>
                        <option value="PA" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'PA') ? 'selected' : '' ?>>Pará (PA)</option>
                        <option value="PB" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'PB') ? 'selected' : '' ?>>Paraíba (PB)</option>
                        <option value="PR" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'PR') ? 'selected' : '' ?>>Paraná (PR)</option>
                        <option value="PE" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'PE') ? 'selected' : '' ?>>Pernambuco (PE)</option>
                        <option value="PI" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'PI') ? 'selected' : '' ?>>Piauí (PI)</option>
                        <option value="RJ" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'RJ') ? 'selected' : '' ?>>Rio de Janeiro (RJ)</option>
                        <option value="RN" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'RN') ? 'selected' : '' ?>>Rio Grande do Norte (RN)</option>
                        <option value="RS" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'RS') ? 'selected' : '' ?>>Rio Grande do Sul (RS)</option>
                        <option value="RO" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'RO') ? 'selected' : '' ?>>Rondônia (RO)</option>
                        <option value="RR" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'RR') ? 'selected' : '' ?>>Roraima (RR)</option>
                        <option value="SC" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'SC') ? 'selected' : '' ?>>Santa Catarina (SC)</option>
                        <option value="SP" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'SP') ? 'selected' : '' ?>>São Paulo (SP)</option>
                        <option value="SE" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'SE') ? 'selected' : '' ?>>Sergipe (SE)</option>
                        <option value="TO" <?= (isset($participantes[$i]['estado']) && $participantes[$i]['estado'] === 'TO') ? 'selected' : '' ?>>Tocantins (TO)</option>

                    </select><br>

                    <label for="dependencia_<?= $i ?>">Dependência administrativa da escola:</label>
                    <select id="dependencia_<?= $i ?>" name="dependencia[<?= $i ?>]" <?= $i <= 2 ? 'required' : '' ?>>
                        <option value="">Selecione</option>
                        <option value="Federal" <?= (isset($participantes[$i]['dependencia']) && $participantes[$i]['dependencia'] === 'Federal') ? 'selected' : '' ?>>Federal</option>
                        <option value="Estadual" <?= (isset($participantes[$i]['dependencia']) && $participantes[$i]['dependencia'] === 'Estadual') ? 'selected' : '' ?>>Estadual</option>
                        <option value="Municipal" <?= (isset($participantes[$i]['dependencia']) && $participantes[$i]['dependencia'] === 'Municipal') ? 'selected' : '' ?>>Municipal</option>
                        <option value="Privada" <?= (isset($participantes[$i]['dependencia']) && $participantes[$i]['dependencia'] === 'Privada') ? 'selected' : '' ?>>Privada</option>
                    </select><br>
                    <hr>



                <?php endfor; ?>
                <h3>Dados do Projeto</h3>

                <label>Título:</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($projeto['titulo'] ?? '') ?>" required><br>

                <label>Área:</label>
                <input type="text" name="categoria" value="<?= htmlspecialchars($projeto['categoria'] ?? '') ?>" required><br>

                <label>Projeto Integrador:</label>
                <input type="text" name="projeto_integrador" value="<?= htmlspecialchars($projeto['projeto_integrador'] ?? '') ?>"><br>

                <label>Resumo:</label><br>
                <textarea name="resumo_texto" rows="5" cols="60" required><?= htmlspecialchars($projeto['resumo_texto'] ?? '') ?></textarea><br>

                <label>Palavras-chave:</label>
                <input type="text" name="palavras_chave" value="<?= htmlspecialchars($projeto['palavras_chave'] ?? '') ?>" required><br>

                <label>Link do Vídeo do Projeto:</label>
                <input type="url" name="link_video" value="<?= htmlspecialchars($projeto['link_video'] ?? '') ?>"><br>

                <label>Link do Pôster do Projeto:</label>
                <input type="url" name="link_poster" value="<?= htmlspecialchars($projeto['link_poster'] ?? '') ?>"><br>

                <label>Nome do Orientador:</label>
                <input type="text" name="nome_orientador" value="<?= htmlspecialchars($orientadores['nome_orientador'] ?? '') ?>" required><br>

                <label>Email do Orientador:</label>
                <input type="email" name="email_orientador" value="<?= htmlspecialchars($orientadores['email_orientador'] ?? '') ?>" required><br>

                <label>Instituição:</label>
                <input type="text" name="instituicao_orientador" value="<?= htmlspecialchars($orientadores['instituicao_orientador'] ?? '') ?>" required><br>

                <label>Nome do Coorientador:</label>
                <input type="text" name="co_nome" value="<?= htmlspecialchars($orientadores['co_nome'] ?? '') ?>"><br>

                <label>CPF do Coorientador:</label>
                <input type="text" name="co_cpf" value="<?= htmlspecialchars($orientadores['co_cpf'] ?? '') ?>"><br>

                <label>Email do Coorientador:</label>
                <input type="text" name="co_email" value="<?= htmlspecialchars($orientadores['co_email'] ?? '') ?>"><br>

                <hr>


                <input type="submit" value="Salvar Alterações">
            </form>
        <?php endif; ?>
    </div>
</body>


</html>

<?php
$conn = null;
?>