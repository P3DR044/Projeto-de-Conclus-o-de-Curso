<?php
include_once('../../config/session.php');
include_once('../../config/database.php');


verificarLogin();

$database = new Database();
$conn = $database->getConnection();

$stmtData = $conn->prepare("SELECT data_inicio, data_fim FROM configuracoes_submissao LIMIT 1");
$stmtData->execute();
$datas = $stmtData->fetch(PDO::FETCH_ASSOC);

$hoje = new DateTime();

$periodo_aberto = false;
$dataInicio = null;
$dataFim = null;

if ($datas) {
    $dataInicio = new DateTime($datas['data_inicio']);
    $dataFim = new DateTime($datas['data_fim']);
    if ($hoje >= $dataInicio && $hoje <= $dataFim) {
        $periodo_aberto = true;
    }
} else {

    $periodo_aberto = false;
}


$etapa = isset($_GET['etapa']) ? intval($_GET['etapa']) : 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (!isset($_SESSION['id_projeto']) && $etapa == 1) {

        $stmt = $conn->prepare("INSERT INTO projetos (titulo, categoria, projeto_integrador, resumo_arquivo, link_poster, link_video, usuario_id, status)
        VALUES (:titulo, :categoria, :projeto_integrador, '', '', '', :usuario_id, 'pendente')");
        $stmt->execute([
            ':titulo' => 'Título Temporário',
            ':categoria' => 'Categoria Temporária',
            ':projeto_integrador' => '',
            ':usuario_id' => $_SESSION['usuario_id']
        ]);

        $_SESSION['id_projeto'] = $conn->lastInsertId();
    } elseif (!isset($_SESSION['id_projeto'])) {

        die('Erro: Projeto não iniciado corretamente. Por favor, inicie no passo 1.');
    }



    // Etapa 1 - Criar o projeto e inserir participante 1
    if ($etapa == 1) {
        // Cria o projeto primeiro (dados mínimos para gerar o ID)
        // Verifica se já existe um projeto em andamento na sessão
        if (!isset($_SESSION['id_projeto'])) {
            // Cria o projeto apenas se ainda não existir
            $stmt = $conn->prepare("INSERT INTO projetos (titulo, categoria, projeto_integrador, resumo_arquivo, link_poster, link_video, usuario_id, status)
    VALUES (:titulo, :categoria, :projeto_integrador, '', '', '', :usuario_id, 'pendente')");
            $stmt->execute([
                ':titulo' => 'Título Temporário',
                ':categoria' => 'Categoria Temporária',
                ':projeto_integrador' => '',
                ':usuario_id' => $_SESSION['usuario_id']
            ]);

            $_SESSION['id_projeto'] = $conn->lastInsertId();
        }
        // Insere o Participante 1
        $stmt = $conn->prepare("INSERT INTO participantes (
    id_projeto, nome_completo, nome_social, cpf, email, telefone, data_nascimento,
    tipo_curso, serie, instituicao, municipio, estado, cidade, dependencia, 
    sexo, identidade_genero, etnia, termo
) VALUES (
    :id_projeto, :nome_completo, :nome_social, :cpf, :email, :telefone, :data_nascimento,
    :tipo_curso, :serie, :instituicao, :municipio, :estado, :cidade, :dependencia,
    :sexo, :identidade_genero, :etnia, :termo
)");

        $stmt->execute([
            ':id_projeto' => $_SESSION['id_projeto'],
            ':nome_completo' => $_POST['nome_completo'],
            ':nome_social' => $_POST['nome_social'] ?? null,
            ':cpf' => $_POST['cpf'],
            ':email' => $_POST['email'],
            ':telefone' => $_POST['telefone'],
            ':data_nascimento' => $_POST['data_nascimento'],
            ':tipo_curso' => $_POST['tipo_curso'],
            ':serie' => $_POST['serie'],
            ':instituicao' => $_POST['instituicao'],
            ':municipio' => $_POST['municipio'],
            ':estado' => $_POST['estado'],
            ':cidade' => $_POST['cidade'],
            ':dependencia' => $_POST['dependencia'],
            ':sexo' => $_POST['sexo'] ?? null,
            ':identidade_genero' => $_POST['identidade_genero'] ?? null,
            ':etnia' => $_POST['etnia'] ?? null,
            ':termo' => isset($_POST['termo']) ? 1 : 0,
        ]);

        $conn = null;
        header("Location: criar.php?etapa=2");
        exit();
    }

    // Etapa 2 - Inserir participante 2
    if ($etapa >= 2 && $etapa <= 5) {
        $i = $etapa; // pega o número da etapa atual (2, 3, 4 ou 5)

        if (!empty($_POST["nome_completo{$i}"])) {
            $stmt = $conn->prepare("INSERT INTO participantes (
            id_projeto, nome_completo, nome_social, cpf, email, telefone, data_nascimento,
            tipo_curso, serie, instituicao, municipio, estado, cidade, dependencia,
            sexo, identidade_genero, etnia, termo
        ) VALUES (
            :id_projeto, :nome_completo, :nome_social, :cpf, :email, :telefone, :data_nascimento,
            :tipo_curso, :serie, :instituicao, :municipio, :estado, :cidade, :dependencia,
            :sexo, :identidade_genero, :etnia, :termo
        )");

            $stmt->execute([
                ':id_projeto' => $_SESSION['id_projeto'],
                ':nome_completo' => $_POST["nome_completo{$i}"],
                ':nome_social' => $_POST["nome_social{$i}"] ?? null,
                ':cpf' => $_POST["cpf{$i}"] ?? null,
                ':email' => $_POST["email{$i}"] ?? null,
                ':telefone' => $_POST["telefone{$i}"] ?? null,
                ':data_nascimento' => $_POST["data_nascimento{$i}"] ?? null,
                ':tipo_curso' => $_POST["tipo_curso{$i}"] ?? null,
                ':serie' => $_POST["serie{$i}"] ?? null,
                ':instituicao' => $_POST["instituicao{$i}"] ?? null,
                ':municipio' => $_POST["municipio{$i}"] ?? null,
                ':estado' => $_POST["estado{$i}"] ?? null,
                ':cidade' => $_POST["cidade{$i}"] ?? null,
                ':dependencia' => $_POST["dependencia{$i}"] ?? null,
                ':sexo' => $_POST["sexo{$i}"] ?? null,
                ':identidade_genero' => $_POST["identidade_genero{$i}"] ?? null,
                ':etnia' => $_POST["etnia{$i}"] ?? null,
                ':termo' => 0
            ]);
        }



        // Redireciona para próxima etapa
        $proximaEtapa = $etapa + 1;
        header("Location: criar.php?etapa={$proximaEtapa}");
        exit();
    }

    // Etapa 6 - Inserir orientador e coorientador
    if ($etapa == 6) {
        $stmt = $conn->prepare("INSERT INTO orientadores (id_projeto, nome_orientador, cpf_orientador, email_orientador, instituicao_orientador, co_nome, co_cpf, co_email)
            VALUES (:id_projeto, :nome_orientador, :cpf_orientador, :email_orientador, :instituicao_orientador, :co_nome, :co_cpf, :co_email)");

        $stmt->execute([
            ':id_projeto' => $_SESSION['id_projeto'],
            ':nome_orientador' => $_POST['orientador_nome'],
            ':cpf_orientador' => $_POST['orientador_cpf'],
            ':email_orientador' => $_POST['orientador_email'],
            ':instituicao_orientador' => $_POST['orientador_instituicao'],
            ':co_nome' => $_POST['coorientador_nome'] ?? null,
            ':co_cpf' => $_POST['coorientador_cpf'] ?? null,
            ':co_email' => $_POST['coorientador_email'] ?? null,
        ]);

        $conn = null;
        header("Location: criar.php?etapa=7");
        exit();
    }

    // Etapa 7 - Atualizar projeto com os dados completos e fazer upload do arquivo
    if ($etapa == 7) {
        // Recebe os dados do formulário texto
        $resumo_texto = $_POST['resumo_texto'] ?? '';
        $palavras_chave = $_POST['palavras_chave'] ?? '';

        // Atualiza os dados do projeto existente
        $sql = "UPDATE projetos SET 
                titulo = :titulo, 
                categoria = :categoria, 
                projeto_integrador = :projeto_integrador,
                resumo_texto = :resumo_texto,
                palavras_chave = :palavras_chave,
                link_video = :link_video,
                link_poster = :link_poster
            WHERE id = :id_projeto";

        $stmt = $conn->prepare($sql);

        $params = [
            ':titulo' => $_POST['titulo'],
            ':categoria' => $_POST['categoria'],
            ':projeto_integrador' => $_POST['projeto_integrador'],
            ':resumo_texto' => $resumo_texto,
            ':palavras_chave' => $palavras_chave,
            ':link_video' => $_POST['link_video'],
            ':link_poster' => $_POST['link_poster'],
            ':id_projeto' => $_SESSION['id_projeto']
        ];

        $stmt->execute($params);

        $conn = null;
        unset($_SESSION['id_projeto']);
        $_SESSION['message'] = "Projeto submetido com sucesso!";
        $_SESSION['message_type'] = "success";
        header("Location: listar.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submeter Projeto</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: black;
        }

        .spot {
            width: 250px;
            height: 100vh;
            background-color: #CD853F;
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
        }

        form {
            max-width: 800px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: grid;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input,
        select,
        button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            background-color: #3498db;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .form-group p {
            margin: 0;
            font-size: 14px;
            color: #333;
        }

        .form-group a {
            color: #1a0dab;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
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
    <div class="main-content">
        <h1>Submeter um Projeto</h1>

        <?php if (!$periodo_aberto): ?>
            <div style="color:red; font-weight:bold;">
                Submissão de projetos está fechada no momento.<br />
                Período permitido: <?= $dataInicio ? $dataInicio->format('d/m/Y') : 'N/A' ?> até <?= $dataFim ? $dataFim->format('d/m/Y') : 'N/A' ?>
            </div>
        <?php else: ?>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert <?php echo $_SESSION['message_type'] == 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php endif; ?>


            <?php if ($etapa == 1): ?>

                <h2>Etapa 1: Dados do Integrante 1</h2>
                <form method="POST" action="criar.php?etapa=1">
                    <label for="nome_completo">Nome completo:</label>
                    <input type="text" id="nome_completo" name="nome_completo" required maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{3,100}" title="Somente letras e espaços, mínimo de 3 caracteres.">

                    <label for="nome_social">Nome social (se houver):</label>
                    <input type="text" id="nome_social" name="nome_social" maxlength="100">

                    <label for="sexo">Sexo:</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Intersexo">Intersexo</option>
                    </select>

                    <label for="identidade_genero">Identidade de Gênero:</label>
                    <select id="identidade_genero" name="identidade_genero" required>
                        <option value="">Selecione</option>
                        <option value="Homem cis">Homem cisgênero</option>
                        <option value="Homem trans">Homem transgênero</option>
                        <option value="Mulher cis">Mulher cisgênero</option>
                        <option value="Mulher trans">Mulher transgênero</option>
                        <option value="Não-binário">Não-binário</option>
                        <option value="Outro">Outro</option>
                    </select>

                    <label for="etnia">Como você se autodeclara?</label>
                    <select id="etnia" name="etnia" required>
                        <option value="">Selecione</option>
                        <option value="Branca">Branco</option>
                        <option value="Preta">Preto</option>
                        <option value="Parda">Pardo</option>
                        <option value="Amarela">Amarelo</option>
                        <option value="Indígena">Indígena</option>
                    </select>

                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" required maxlength="14" pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}" title="Digite um CPF válido no formato 000.000.000-00 ou apenas números." placeholder="000.000.000-00">

                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required maxlength="100">

                    <label for="telefone">Telefone (Whatsapp):</label>
                    <input type="text" id="telefone" name="telefone" required maxlength="15" pattern="\(?\d{2}\)?\s?\d{4,5}-?\d{4}" title="Digite um número válido com DDD. Ex: (11) 91234-5678">

                    <label for="data_nascimento">Data de nascimento:</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" required min="1900-01-01" max="<?= date('Y-m-d') ?>">

                    <label for="cidade">Cidade onde reside:</label>
                    <input type="text" id="cidade" name="cidade" required maxlength="100">

                    <label for="tipo_curso">Tipo de Curso:</label>
                    <select id="tipo_curso" name="tipo_curso" required>
                        <option value="">Selecione</option>
                        <option value="Ensino Médio">Ensino Médio</option>
                        <option value="Ensino Técnico Integrado">Ensino Técnico Integrado</option>
                        <option value="Ensino Técnico Concomitante/Subsequente">Ensino Técnico Concomitante/Subsequente</option>
                    </select>

                    <label for="serie">Ano / série / semestre / ou módulo que está cursando:</label>
                    <select id="serie" name="serie" required>
                        <option value="">Selecione</option>
                        <option value="1º ano do Médio">1º ano do Médio</option>
                        <option value="2º ano do Médio">2º ano do Médio</option>
                        <option value="3º ano do Médio">3º ano do Médio</option>
                        <option value="4º ano do Médio">4º ano do Médio</option>
                        <option value="1º semestre">1º semestre</option>
                        <option value="2º semestre">2º semestre</option>
                        <option value="3º semestre">3º semestre</option>
                        <option value="4º semestre">4º semestre</option>
                        <option value="5º semestre">5º semestre</option>
                        <option value="6º semestre">6º semestre</option>
                        <option value="Concluído">Concluído</option>
                    </select>

                    <label for="instituicao">Instituição de Ensino:</label>
                    <input type="text" id="instituicao" name="instituicao" required maxlength="100">

                    <label for="municipio">Município da Instituição de Ensino:</label>
                    <input type="text" id="municipio" name="municipio" required maxlength="100">

                    <label for="estado">Estado da sua Instituição:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecione</option>
                        <option value="AC">Acre (AC)</option>
                        <option value="AL">Alagoas (AL)</option>
                        <option value="AP">Amapá (AP)</option>
                        <option value="AM">Amazonas (AM)</option>
                        <option value="BA">Bahia (BA)</option>
                        <option value="CE">Ceará (CE)</option>
                        <option value="DF">Distrito Federal (DF)</option>
                        <option value="ES">Espírito Santo (ES)</option>
                        <option value="GO">Goiás (GO)</option>
                        <option value="MA">Maranhão (MA)</option>
                        <option value="MT">Mato Grosso (MT)</option>
                        <option value="MS">Mato Grosso do Sul (MS)</option>
                        <option value="MG">Minas Gerais (MG)</option>
                        <option value="PA">Pará (PA)</option>
                        <option value="PB">Paraíba (PB)</option>
                        <option value="PR">Paraná (PR)</option>
                        <option value="PE">Pernambuco (PE)</option>
                        <option value="PI">Piauí (PI)</option>
                        <option value="RJ">Rio de Janeiro (RJ)</option>
                        <option value="RN">Rio Grande do Norte (RN)</option>
                        <option value="RS">Rio Grande do Sul (RS)</option>
                        <option value="RO">Rondônia (RO)</option>
                        <option value="RR">Roraima (RR)</option>
                        <option value="SC">Santa Catarina (SC)</option>
                        <option value="SP">São Paulo (SP)</option>
                        <option value="SE">Sergipe (SE)</option>
                        <option value="TO">Tocantins (TO)</option>
                    </select>

                    <label for="dependencia">Dependência administrativa da escola:</label>
                    <select id="dependencia" name="dependencia" required>
                        <option value="">Selecione</option>
                        <option value="Federal">Federal</option>
                        <option value="Estadual">Estadual</option>
                        <option value="Municipal">Municipal</option>
                        <option value="Privada">Privada</option>
                    </select>

                    <label>
                        <input type="checkbox" name="termo" required>
                        Ao inscrever este trabalho, concordamos com o regulamento da FECCIF25. Declaramos que todas as informações contidas neste formulário de inscrição estão corretas, e que o resumo e o pôster estão relacionados apenas ao trabalho desenvolvido pelos autores. Estamos cientes de que a não veracidade das informações fornecidas aqui poderá implicar na desclassificação do projeto.
                    </label>

                    <button type="submit">Próxima</button>
                </form>

                <script>
                    // Máscara para CPF
                    document.getElementById('cpf').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    });

                    // Máscara para telefone
                    document.getElementById('telefone').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        if (value.length <= 10) {
                            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                        } else {
                            value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                        }
                        e.target.value = value;
                    });
                </script>
            <?php endif; ?>



            <?php if ($etapa == 2): ?>

                <h2>Etapa 2: Dados do Integrante 2 (opcional)</h2>
                <form method="POST" action="criar.php?etapa=2">
                    <label for="nome_completo2">Nome completo:</label>
                    <input type="text" id="nome_completo2" name="nome_completo2" maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{3,100}" title="Somente letras e espaços, mínimo de 3 caracteres.">

                    <label for="nome_social2">Nome social (se houver):</label>
                    <input type="text" id="nome_social2" name="nome_social2" maxlength="100">

                    <label for="sexo2">Sexo:</label>
                    <select id="sexo2" name="sexo2" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Intersexo">Intersexo</option>
                    </select>

                    <label for="identidade_genero2">Identidade de Gênero:</label>
                    <select id="identidade_genero2" name="identidade_genero2" required>
                        <option value="">Selecione</option>
                        <option value="Homem cis">Homem cisgênero</option>
                        <option value="Homem trans">Homem transgênero</option>
                        <option value="Mulher cis">Mulher cisgênero</option>
                        <option value="Mulher trans">Mulher transgênero</option>
                        <option value="Não-binário">Não-binário</option>
                        <option value="Outro">Outro</option>
                    </select>

                    <label for="etnia2">Como você se autodeclara?</label>
                    <select id="etnia2" name="etnia2" required>
                        <option value="">Selecione</option>
                        <option value="Branca">Branco</option>
                        <option value="Preta">Preto</option>
                        <option value="Parda">Pardo</option>
                        <option value="Amarela">Amarelo</option>
                        <option value="Indígena">Indígena</option>
                    </select>

                    <label for="cpf2">CPF:</label>
                    <input type="text" id="cpf2" name="cpf2" maxlength="14" pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}" title="Digite um CPF válido no formato 000.000.000-00 ou apenas números." placeholder="000.000.000-00">

                    <label for="email2">E-mail:</label>
                    <input type="email" id="email2" name="email2" maxlength="100">

                    <label for="telefone2">Telefone (Whatsapp):</label>
                    <input type="text" id="telefone2" name="telefone2" maxlength="15" pattern="\(?\d{2}\)?\s?\d{4,5}-?\d{4}" title="Digite um número válido com DDD. Ex: (11) 91234-5678">

                    <label for="data_nascimento2">Data de nascimento:</label>
                    <input type="date" id="data_nascimento2" name="data_nascimento2" min="1900-01-01" max="<?= date('Y-m-d') ?>">

                    <label for="cidade2">Cidade onde reside:</label>
                    <input type="text" id="cidade2" name="cidade2" maxlength="100">

                    <label for="tipo_curso2">Tipo de Curso:</label>
                    <select id="tipo_curso2" name="tipo_curso2">
                        <option value="">Selecione</option>
                        <option value="Ensino Médio">Ensino Médio</option>
                        <option value="Ensino Técnico Integrado">Ensino Técnico Integrado</option>
                        <option value="Ensino Técnico Concomitante/Subsequente">Ensino Técnico Concomitante/Subsequente</option>
                    </select>

                    <label for="serie2">Ano / série / semestre / ou módulo que está cursando:</label>
                    <select id="serie2" name="serie2" required>
                        <option value="">Selecione</option>
                        <option value="1º ano do Médio">1º ano do Médio</option>
                        <option value="2º ano do Médio">2º ano do Médio</option>
                        <option value="3º ano do Médio">3º ano do Médio</option>
                        <option value="4º ano do Médio">4º ano do Médio</option>
                        <option value="1º semestre">1º semestre</option>
                        <option value="2º semestre">2º semestre</option>
                        <option value="3º semestre">3º semestre</option>
                        <option value="4º semestre">4º semestre</option>
                        <option value="5º semestre">5º semestre</option>
                        <option value="6º semestre">6º semestre</option>
                        <option value="Concluído">Concluído</option>
                    </select>

                    <label for="instituicao2">Instituição de Ensino:</label>
                    <input type="text" id="instituicao2" name="instituicao2" maxlength="100">

                    <label for="municipio2">Município da Instituição de Ensino:</label>
                    <input type="text" id="municipio2" name="municipio2" maxlength="100">

                    <label for="estado2">Estado da sua Instituição:</label>
                    <select id="estado2" name="estado2">
                        <option value="">Selecione</option>
                        <option value="AC">Acre (AC)</option>
                        <option value="AL">Alagoas (AL)</option>
                        <option value="AP">Amapá (AP)</option>
                        <option value="AM">Amazonas (AM)</option>
                        <option value="BA">Bahia (BA)</option>
                        <option value="CE">Ceará (CE)</option>
                        <option value="DF">Distrito Federal (DF)</option>
                        <option value="ES">Espírito Santo (ES)</option>
                        <option value="GO">Goiás (GO)</option>
                        <option value="MA">Maranhão (MA)</option>
                        <option value="MT">Mato Grosso (MT)</option>
                        <option value="MS">Mato Grosso do Sul (MS)</option>
                        <option value="MG">Minas Gerais (MG)</option>
                        <option value="PA">Pará (PA)</option>
                        <option value="PB">Paraíba (PB)</option>
                        <option value="PR">Paraná (PR)</option>
                        <option value="PE">Pernambuco (PE)</option>
                        <option value="PI">Piauí (PI)</option>
                        <option value="RJ">Rio de Janeiro (RJ)</option>
                        <option value="RN">Rio Grande do Norte (RN)</option>
                        <option value="RS">Rio Grande do Sul (RS)</option>
                        <option value="RO">Rondônia (RO)</option>
                        <option value="RR">Roraima (RR)</option>
                        <option value="SC">Santa Catarina (SC)</option>
                        <option value="SP">São Paulo (SP)</option>
                        <option value="SE">Sergipe (SE)</option>
                        <option value="TO">Tocantins (TO)</option>
                    </select>

                    <label for="dependencia2">Dependência administrativa da escola:</label>
                    <select id="dependencia2" name="dependencia2">
                        <option value="">Selecione</option>
                        <option value="Federal">Federal</option>
                        <option value="Estadual">Estadual</option>
                        <option value="Municipal">Municipal</option>
                        <option value="Privada">Privada</option>
                    </select>

                    <button type="submit">Próxima</button>
                </form>

                <script>
                    // Máscara para CPF
                    document.getElementById('cpf2').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    });

                    // Máscara para telefone
                    document.getElementById('telefone2').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        if (value.length <= 10) {
                            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                        } else {
                            value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                        }
                        e.target.value = value;
                    });
                </script>

            <?php endif; ?>

            <?php if ($etapa == 3): ?>

                <h2>Etapa 3: Dados do Integrante 3 (opcional)</h2>
                <form method="POST" action="criar.php?etapa=3">
                    <label for="nome_completo3">Nome completo:</label>
                    <input type="text" id="nome_completo3" name="nome_completo3" maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{3,100}" title="Somente letras e espaços, mínimo de 3 caracteres.">

                    <label for="nome_social3">Nome social (se houver):</label>
                    <input type="text" id="nome_social3" name="nome_social3" maxlength="100">

                    <label for="sexo3">Sexo:</label>
                    <select id="sexo3" name="sexo3" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Intersexo">Intersexo</option>
                    </select>

                    <label for="identidade_genero3">Identidade de Gênero:</label>
                    <select id="identidade_genero3" name="identidade_genero3" required>
                        <option value="">Selecione</option>
                        <option value="Homem cis">Homem cisgênero</option>
                        <option value="Homem trans">Homem transgênero</option>
                        <option value="Mulher cis">Mulher cisgênero</option>
                        <option value="Mulher trans">Mulher transgênero</option>
                        <option value="Não-binário">Não-binário</option>
                        <option value="Outro">Outro</option>
                    </select>

                    <label for="etnia3">Como você se autodeclara?</label>
                    <select id="etnia3" name="etnia3" required>
                        <option value="">Selecione</option>
                        <option value="Branca">Branco</option>
                        <option value="Preta">Preto</option>
                        <option value="Parda">Pardo</option>
                        <option value="Amarela">Amarelo</option>
                        <option value="Indígena">Indígena</option>
                    </select>

                    <label for="cpf3">CPF:</label>
                    <input type="text" id="cpf3" name="cpf3" maxlength="14" pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}" title="Digite um CPF válido no formato 000.000.000-00 ou apenas números." placeholder="000.000.000-00">

                    <label for="email3">E-mail:</label>
                    <input type="email" id="email3" name="email3" maxlength="100">

                    <label for="telefone3">Telefone (Whatsapp):</label>
                    <input type="text" id="telefone3" name="telefone3" maxlength="15" pattern="\(?\d{2}\)?\s?\d{4,5}-?\d{4}" title="Digite um número válido com DDD. Ex: (11) 91234-5678">

                    <label for="data_nascimento3">Data de nascimento:</label>
                    <input type="date" id="data_nascimento3" name="data_nascimento3" min="1900-01-01" max="<?= date('Y-m-d') ?>">

                    <label for="cidade3">Cidade onde reside:</label>
                    <input type="text" id="cidade3" name="cidade3" maxlength="100">

                    <label for="tipo_curso3">Tipo de Curso:</label>
                    <select id="tipo_curso3" name="tipo_curso3">
                        <option value="">Selecione</option>
                        <option value="Ensino Médio">Ensino Médio</option>
                        <option value="Ensino Técnico Integrado">Ensino Técnico Integrado</option>
                        <option value="Ensino Técnico Concomitante/Subsequente">Ensino Técnico Concomitante/Subsequente</option>
                    </select>

                    <label for="serie3">Ano / série / semestre / ou módulo que está cursando:</label>
                    <select id="serie3" name="serie3" required>
                        <option value="">Selecione</option>
                        <option value="1º ano do Médio">1º ano do Médio</option>
                        <option value="2º ano do Médio">2º ano do Médio</option>
                        <option value="3º ano do Médio">3º ano do Médio</option>
                        <option value="4º ano do Médio">4º ano do Médio</option>
                        <option value="1º semestre">1º semestre</option>
                        <option value="2º semestre">2º semestre</option>
                        <option value="3º semestre">3º semestre</option>
                        <option value="4º semestre">4º semestre</option>
                        <option value="5º semestre">5º semestre</option>
                        <option value="6º semestre">6º semestre</option>
                        <option value="Concluído">Concluído</option>
                    </select>


                    <label for="instituicao3">Instituição de Ensino:</label>
                    <input type="text" id="instituicao3" name="instituicao3" maxlength="100">

                    <label for="municipio3">Município da Instituição de Ensino:</label>
                    <input type="text" id="municipio3" name="municipio3" maxlength="100">

                    <label for="estado3">Estado da sua Instituição:</label>
                    <select id="estado3" name="estado3">
                        <option value="">Selecione</option>
                        <option value="AC">Acre (AC)</option>
                        <option value="AL">Alagoas (AL)</option>
                        <option value="AP">Amapá (AP)</option>
                        <option value="AM">Amazonas (AM)</option>
                        <option value="BA">Bahia (BA)</option>
                        <option value="CE">Ceará (CE)</option>
                        <option value="DF">Distrito Federal (DF)</option>
                        <option value="ES">Espírito Santo (ES)</option>
                        <option value="GO">Goiás (GO)</option>
                        <option value="MA">Maranhão (MA)</option>
                        <option value="MT">Mato Grosso (MT)</option>
                        <option value="MS">Mato Grosso do Sul (MS)</option>
                        <option value="MG">Minas Gerais (MG)</option>
                        <option value="PA">Pará (PA)</option>
                        <option value="PB">Paraíba (PB)</option>
                        <option value="PR">Paraná (PR)</option>
                        <option value="PE">Pernambuco (PE)</option>
                        <option value="PI">Piauí (PI)</option>
                        <option value="RJ">Rio de Janeiro (RJ)</option>
                        <option value="RN">Rio Grande do Norte (RN)</option>
                        <option value="RS">Rio Grande do Sul (RS)</option>
                        <option value="RO">Rondônia (RO)</option>
                        <option value="RR">Roraima (RR)</option>
                        <option value="SC">Santa Catarina (SC)</option>
                        <option value="SP">São Paulo (SP)</option>
                        <option value="SE">Sergipe (SE)</option>
                        <option value="TO">Tocantins (TO)</option>
                    </select>

                    <label for="dependencia3">Dependência administrativa da escola:</label>
                    <select id="dependencia3" name="dependencia3">
                        <option value="">Selecione</option>
                        <option value="Federal">Federal</option>
                        <option value="Estadual">Estadual</option>
                        <option value="Municipal">Municipal</option>
                        <option value="Privada">Privada</option>
                    </select>

                    <button type="submit">Próxima</button>
                </form>

                <script>
                    // Máscara para CPF
                    document.getElementById('cpf3').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    });

                    // Máscara para telefone
                    document.getElementById('telefone3').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        if (value.length <= 10) {
                            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                        } else {
                            value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                        }
                        e.target.value = value;
                    });
                </script>

            <?php endif; ?>

            <?php if ($etapa == 4): ?>

                <h2>Etapa 4: Dados do Integrante 4 (opcional)</h2>
                <form method="POST" action="criar.php?etapa=4">
                    <label for="nome_completo4">Nome completo:</label>
                    <input type="text" id="nome_completo4" name="nome_completo4" maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{3,100}" title="Somente letras e espaços, mínimo de 3 caracteres.">

                    <label for="nome_social4">Nome social (se houver):</label>
                    <input type="text" id="nome_social4" name="nome_social4" maxlength="100">

                    <label for="sexo4">Sexo:</label>
                    <select id="sexo4" name="sexo4" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Intersexo">Intersexo</option>
                    </select>

                    <label for="identidade_genero4">Identidade de Gênero:</label>
                    <select id="identidade_genero4" name="identidade_genero4" required>
                        <option value="">Selecione</option>
                        <option value="Homem cis">Homem cisgênero</option>
                        <option value="Homem trans">Homem transgênero</option>
                        <option value="Mulher cis">Mulher cisgênero</option>
                        <option value="Mulher trans">Mulher transgênero</option>
                        <option value="Não-binário">Não-binário</option>
                        <option value="Outro">Outro</option>
                    </select>

                    <label for="etnia4">Como você se autodeclara?</label>
                    <select id="etnia4" name="etnia4" required>
                        <option value="">Selecione</option>
                        <option value="Branca">Branco</option>
                        <option value="Preta">Preto</option>
                        <option value="Parda">Pardo</option>
                        <option value="Amarela">Amarelo</option>
                        <option value="Indígena">Indígena</option>
                    </select>

                    <label for="cpf4">CPF:</label>
                    <input type="text" id="cpf4" name="cpf4" maxlength="14" pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}" title="Digite um CPF válido no formato 000.000.000-00 ou apenas números." placeholder="000.000.000-00">

                    <label for="email4">E-mail:</label>
                    <input type="email" id="email4" name="email4" maxlength="100">

                    <label for="telefone4">Telefone (Whatsapp):</label>
                    <input type="text" id="telefone4" name="telefone4" maxlength="15" pattern="\(?\d{2}\)?\s?\d{4,5}-?\d{4}" title="Digite um número válido com DDD. Ex: (11) 91234-5678">

                    <label for="data_nascimento4">Data de nascimento:</label>
                    <input type="date" id="data_nascimento4" name="data_nascimento4" min="1900-01-01" max="<?= date('Y-m-d') ?>">

                    <label for="cidade4">Cidade onde reside:</label>
                    <input type="text" id="cidade4" name="cidade4" maxlength="100">

                    <label for="tipo_curso4">Tipo de Curso:</label>
                    <select id="tipo_curso4" name="tipo_curso4">
                        <option value="">Selecione</option>
                        <option value="Ensino Médio">Ensino Médio</option>
                        <option value="Ensino Técnico Integrado">Ensino Técnico Integrado</option>
                        <option value="Ensino Técnico Concomitante/Subsequente">Ensino Técnico Concomitante/Subsequente</option>
                    </select>

                    <label for="serie4">Ano / série / semestre / ou módulo que está cursando:</label>
                    <select id="serie4" name="serie4" required>
                        <option value="">Selecione</option>
                        <option value="1º ano do Médio">1º ano do Médio</option>
                        <option value="2º ano do Médio">2º ano do Médio</option>
                        <option value="3º ano do Médio">3º ano do Médio</option>
                        <option value="4º ano do Médio">4º ano do Médio</option>
                        <option value="1º semestre">1º semestre</option>
                        <option value="2º semestre">2º semestre</option>
                        <option value="3º semestre">3º semestre</option>
                        <option value="4º semestre">4º semestre</option>
                        <option value="5º semestre">5º semestre</option>
                        <option value="6º semestre">6º semestre</option>
                        <option value="Concluído">Concluído</option>
                    </select>

                    <label for="instituicao4">Instituição de Ensino:</label>
                    <input type="text" id="instituicao4" name="instituicao4" maxlength="100">

                    <label for="municipio4">Município da Instituição de Ensino:</label>
                    <input type="text" id="municipio4" name="municipio4" maxlength="100">

                    <label for="estado4">Estado da sua Instituição:</label>
                    <select id="estado4" name="estado4">
                        <option value="">Selecione</option>
                        <option value="AC">Acre (AC)</option>
                        <option value="AL">Alagoas (AL)</option>
                        <option value="AP">Amapá (AP)</option>
                        <option value="AM">Amazonas (AM)</option>
                        <option value="BA">Bahia (BA)</option>
                        <option value="CE">Ceará (CE)</option>
                        <option value="DF">Distrito Federal (DF)</option>
                        <option value="ES">Espírito Santo (ES)</option>
                        <option value="GO">Goiás (GO)</option>
                        <option value="MA">Maranhão (MA)</option>
                        <option value="MT">Mato Grosso (MT)</option>
                        <option value="MS">Mato Grosso do Sul (MS)</option>
                        <option value="MG">Minas Gerais (MG)</option>
                        <option value="PA">Pará (PA)</option>
                        <option value="PB">Paraíba (PB)</option>
                        <option value="PR">Paraná (PR)</option>
                        <option value="PE">Pernambuco (PE)</option>
                        <option value="PI">Piauí (PI)</option>
                        <option value="RJ">Rio de Janeiro (RJ)</option>
                        <option value="RN">Rio Grande do Norte (RN)</option>
                        <option value="RS">Rio Grande do Sul (RS)</option>
                        <option value="RO">Rondônia (RO)</option>
                        <option value="RR">Roraima (RR)</option>
                        <option value="SC">Santa Catarina (SC)</option>
                        <option value="SP">São Paulo (SP)</option>
                        <option value="SE">Sergipe (SE)</option>
                        <option value="TO">Tocantins (TO)</option>
                    </select>

                    <label for="dependencia4">Dependência administrativa da escola:</label>
                    <select id="dependencia4" name="dependencia4">
                        <option value="">Selecione</option>
                        <option value="Federal">Federal</option>
                        <option value="Estadual">Estadual</option>
                        <option value="Municipal">Municipal</option>
                        <option value="Privada">Privada</option>
                    </select>

                    <button type="submit">Próxima</button>
                </form>

                <script>
                    // Máscara para CPF
                    document.getElementById('cpf4').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    });

                    // Máscara para telefone
                    document.getElementById('telefone4').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        if (value.length <= 10) {
                            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                        } else {
                            value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                        }
                        e.target.value = value;
                    });
                </script>

            <?php endif; ?>

            <?php if ($etapa == 5): ?>

                <h2>Etapa 5: Dados do Integrante 5 (opcional)</h2>
                <form method="POST" action="criar.php?etapa=5">
                    <label for="nome_completo5">Nome completo:</label>
                    <input type="text" id="nome_completo5" name="nome_completo5" maxlength="100" pattern="[A-Za-zÀ-ÿ\s]{3,100}" title="Somente letras e espaços, mínimo de 3 caracteres.">

                    <label for="nome_social5">Nome social (se houver):</label>
                    <input type="text" id="nome_social5" name="nome_social5" maxlength="100">

                    <label for="sexo5">Sexo:</label>
                    <select id="sexo5" name="sexo5" required>
                        <option value="">Selecione</option>
                        <option value="Masculino">Masculino</option>
                        <option value="Feminino">Feminino</option>
                        <option value="Intersexo">Intersexo</option>
                    </select>

                    <label for="identidade_genero5">Identidade de Gênero:</label>
                    <select id="identidade_genero5" name="identidade_genero5" required>
                        <option value="">Selecione</option>
                        <option value="Homem cis">Homem cisgênero</option>
                        <option value="Homem trans">Homem transgênero</option>
                        <option value="Mulher cis">Mulher cisgênero</option>
                        <option value="Mulher trans">Mulher transgênero</option>
                        <option value="Não-binário">Não-binário</option>
                        <option value="Outro">Outro</option>
                    </select>

                    <label for="etnia5">Como você se autodeclara?</label>
                    <select id="etnia5" name="etnia5" required>
                        <option value="">Selecione</option>
                        <option value="Branca">Branco</option>
                        <option value="Preta">Preto</option>
                        <option value="Parda">Pardo</option>
                        <option value="Amarela">Amarelo</option>
                        <option value="Indígena">Indígena</option>
                    </select>

                    <label for="cpf5">CPF:</label>
                    <input type="text" id="cpf5" name="cpf5" maxlength="14" pattern="\d{3}\.?\d{3}\.?\d{3}-?\d{2}" title="Digite um CPF válido no formato 000.000.000-00 ou apenas números." placeholder="000.000.000-00">

                    <label for="email5">E-mail:</label>
                    <input type="email" id="email5" name="email5" maxlength="100">

                    <label for="telefone5">Telefone (Whatsapp):</label>
                    <input type="text" id="telefone5" name="telefone5" maxlength="15" pattern="\(?\d{2}\)?\s?\d{4,5}-?\d{4}" title="Digite um número válido com DDD. Ex: (11) 91234-5678">

                    <label for="data_nascimento5">Data de nascimento:</label>
                    <input type="date" id="data_nascimento5" name="data_nascimento5" min="1900-01-01" max="<?= date('Y-m-d') ?>">

                    <label for="cidade5">Cidade onde reside:</label>
                    <input type="text" id="cidade5" name="cidade5" maxlength="100">

                    <label for="tipo_curso5">Tipo de Curso:</label>
                    <select id="tipo_curso5" name="tipo_curso5">
                        <option value="">Selecione</option>
                        <option value="Ensino Médio">Ensino Médio</option>
                        <option value="Ensino Técnico Integrado">Ensino Técnico Integrado</option>
                        <option value="Ensino Técnico Concomitante/Subsequente">Ensino Técnico Concomitante/Subsequente</option>
                    </select>

                    <label for="serie5">Ano / série / semestre / ou módulo que está cursando:</label>
                    <select id="serie5" name="serie5" required>
                        <option value="">Selecione</option>
                        <option value="1º ano do Médio">1º ano do Médio</option>
                        <option value="2º ano do Médio">2º ano do Médio</option>
                        <option value="3º ano do Médio">3º ano do Médio</option>
                        <option value="4º ano do Médio">4º ano do Médio</option>
                        <option value="1º semestre">1º semestre</option>
                        <option value="2º semestre">2º semestre</option>
                        <option value="3º semestre">3º semestre</option>
                        <option value="4º semestre">4º semestre</option>
                        <option value="5º semestre">5º semestre</option>
                        <option value="6º semestre">6º semestre</option>
                        <option value="Concluído">Concluído</option>
                    </select>

                    <label for="instituicao5">Instituição de Ensino:</label>
                    <input type="text" id="instituicao5" name="instituicao5" maxlength="100">

                    <label for="municipio5">Município da Instituição de Ensino:</label>
                    <input type="text" id="municipio5" name="municipio5" maxlength="100">

                    <label for="estado5">Estado da sua Instituição:</label>
                    <select id="estado5" name="estado5">
                        <option value="">Selecione</option>
                        <option value="AC">Acre (AC)</option>
                        <option value="AL">Alagoas (AL)</option>
                        <option value="AP">Amapá (AP)</option>
                        <option value="AM">Amazonas (AM)</option>
                        <option value="BA">Bahia (BA)</option>
                        <option value="CE">Ceará (CE)</option>
                        <option value="DF">Distrito Federal (DF)</option>
                        <option value="ES">Espírito Santo (ES)</option>
                        <option value="GO">Goiás (GO)</option>
                        <option value="MA">Maranhão (MA)</option>
                        <option value="MT">Mato Grosso (MT)</option>
                        <option value="MS">Mato Grosso do Sul (MS)</option>
                        <option value="MG">Minas Gerais (MG)</option>
                        <option value="PA">Pará (PA)</option>
                        <option value="PB">Paraíba (PB)</option>
                        <option value="PR">Paraná (PR)</option>
                        <option value="PE">Pernambuco (PE)</option>
                        <option value="PI">Piauí (PI)</option>
                        <option value="RJ">Rio de Janeiro (RJ)</option>
                        <option value="RN">Rio Grande do Norte (RN)</option>
                        <option value="RS">Rio Grande do Sul (RS)</option>
                        <option value="RO">Rondônia (RO)</option>
                        <option value="RR">Roraima (RR)</option>
                        <option value="SC">Santa Catarina (SC)</option>
                        <option value="SP">São Paulo (SP)</option>
                        <option value="SE">Sergipe (SE)</option>
                        <option value="TO">Tocantins (TO)</option>
                    </select>

                    <label for="dependencia5">Dependência administrativa da escola:</label>
                    <select id="dependencia5" name="dependencia5">
                        <option value="">Selecione</option>
                        <option value="Federal">Federal</option>
                        <option value="Estadual">Estadual</option>
                        <option value="Municipal">Municipal</option>
                        <option value="Privada">Privada</option>
                    </select>

                    <button type="submit">Próximo</button>
                </form>

                <script>
                    // Máscara para CPF
                    document.getElementById('cpf5').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    });

                    // Máscara para telefone
                    document.getElementById('telefone5').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        if (value.length <= 10) {
                            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                        } else {
                            value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                        }
                        e.target.value = value;
                    });
                </script>

            <?php endif; ?>



            <?php if ($etapa == 6): ?>
                <h2>Etapa 6: Orientador e Coorientador</h2>
                <form method="POST" action="criar.php?etapa=6">
                    <label for="orientador_nome">Nome completo do Orientador:</label>
                    <input type="text" id="orientador_nome" name="orientador_nome" maxlength="100" required>

                    <label for="orientador_cpf">CPF do Orientador:</label>
                    <input type="text" id="orientador_cpf" name="orientador_cpf" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" maxlength="14" placeholder="000.000.000-00" required>

                    <label for="orientador_email">E-mail do Orientador:</label>
                    <input type="email" id="orientador_email" name="orientador_email" maxlength="100" required>

                    <label for="orientador_instituicao">Instituição do Orientador:</label>
                    <input type="text" id="orientador_instituicao" name="orientador_instituicao" maxlength="100" required>

                    <label for="coorientador_nome">Nome completo do Coorientador (se houver):</label>
                    <input type="text" id="coorientador_nome" name="coorientador_nome" maxlength="100">

                    <label for="coorientador_cpf">CPF do Coorientador (se houver):</label>
                    <input type="text" id="coorientador_cpf" name="coorientador_cpf" maxlength="14" placeholder="000.000.000-00">

                    <label for="coorientador_email">E-mail do Coorientador (se houver):</label>
                    <input type="email" id="coorientador_email" name="coorientador_email" maxlength="100">

                    <button type="submit">Próxima</button>
                </form>


                <script>
                    document.getElementById('orientador_cpf', 'coorientador_cpf').addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length > 11) value = value.slice(0, 11);
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d)/, '$1.$2');
                        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = value;
                    });
                </script>
            <?php endif; ?>


            <?php if ($etapa == 7): ?>
                <h2>Etapa 7: Informações do Projeto</h2>
                <form method="POST" action="criar.php?etapa=7">
                    <label for="titulo">Título:</label>
                    <input type="text" id="titulo" name="titulo" maxlength="150" required placeholder="Digite o título do projeto">

                    <label for="categoria">Categoria:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecione</option>
                        <option value="Ciências Agrárias">Ciências Agrárias</option>
                        <option value="Ciências Biológicas">Ciências Biológicas</option>
                        <option value="Ciências Exatas e da Terra">Ciências Exatas e da Terra</option>
                        <option value="Ciências Humanas">Ciências Humanas</option>
                        <option value="Ciências da Saúde">Ciências da Saúde</option>
                        <option value="Ciências Sociais Aplicadas">Ciências Sociais Aplicadas</option>
                        <option value="Engenharias">Engenharias</option>
                        <option value="Linguística, Letras e Artes">Linguística, Letras e Artes</option>
                    </select>

                    <label for="projeto_integrador">Este trabalho é um Projeto Integrador?</label>
                    <p>
                        O Projeto Integrador no IFSP é um componente curricular do qual resulta uma produção acadêmica e técnico-científica que deve estar pautada na indissociabilidade entre ensino, pesquisa e extensão e na integração entre conhecimentos pertinentes tanto à formação geral, quanto à formação específica do curso.
                    </p>
                    <select id="projeto_integrador" name="projeto_integrador" required>
                        <option value="">Selecione</option>
                        <option value="Sim">Sim</option>
                        <option value="Não">Não</option>
                    </select>

                    <label for="resumo_texto"><strong>Texto do <u>RESUMO</u></strong></label>
                    <p>
                        Inserir apenas o texto do RESUMO do trabalho, de acordo com o modelo disponibilizado no site do evento.
                        <strong>Não</strong> inserir as Palavras-chave aqui.
                    </p>
                    <textarea id="resumo_texto" name="resumo_texto" rows="6" maxlength="2000" placeholder="Digite o resumo do projeto..." required></textarea>

                    <div class="form-group">
                        <label for="palavras_chave"><strong>Palavras-chave</strong> do Resumo</label>
                        <p>
                            Inserir 3 (três) palavras-chave separadas por <strong>";"</strong> (ponto e vírgula), escritas em minúscula.
                        </p>
                        <input type="text" id="palavras_chave" name="palavras_chave" maxlength="255" placeholder="ex: inovação; sustentabilidade; energia" required>
                    </div>


                    <label for="link_poster">Link do PÔSTER:</label>
                    <input type="url" id="link_poster" name="link_poster" maxlength="255" placeholder="https://exemplo.com/poster" required pattern="https?://.+">

                    <label for="link_video">Link do VÍDEO:</label>
                    <input type="url" id="link_video" name="link_video" maxlength="255" placeholder="https://exemplo.com/video" required pattern="https?://.+">

                    <div class="form-group">
                        <p><strong>Grupo WhatsApp para Apresentadores de Trabalhos</strong></p>
                        <p>
                            Participe do nosso grupo para apresentadores de trabalhos e encaminhe também para os demais autores do trabalho. Neste grupo será compartilhado todas as informações sobre o evento: <a href="https://chat.whatsapp.com/SEU-LINK-AQUI" target="_blank">Entre no grupo clicando aqui</a>
                        </p>
                    </div>


                    <button type="submit">Submeter Projeto</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>


    </div>
</body>

</html>