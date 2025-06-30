<?php
include_once('../../config/session.php');

verificarLogin();

$servername = "localhost";
$username = "u915057572_Admin";
$password = "F3cc1f2022@";
$dbname = "u915057572_projeto_tcc";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// ID do usuário logado
$id_usuario = $_SESSION['usuario_id'];

// Consulta SQL com junções
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
        WHERE projetos.usuario_id = ?
        GROUP BY projetos.id";


$periodo_aberto = false;

$sqlPeriodo = "SELECT data_inicio, data_fim FROM configuracoes_submissao LIMIT 1";
$resultPeriodo = $conn->query($sqlPeriodo);
if ($resultPeriodo && $resultPeriodo->num_rows > 0) {
    $rowPeriodo = $resultPeriodo->fetch_assoc();
    $hoje = new DateTime();
    $dataInicio = new DateTime($rowPeriodo['data_inicio']);
    $dataFim = new DateTime($rowPeriodo['data_fim']);
    if ($hoje >= $dataInicio && $hoje <= $dataFim) {
        $periodo_aberto = true;
    }
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();



?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos Submetidos</title>
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
            list-style-type: none;
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

        .spot li ul {
            display: none;
            padding-left: 20px;
        }

        .spot li:hover ul {
            display: block;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
            box-sizing: border-box;
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

        .main-content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .main-content table thead tr {
            background-color: #f1f1f1;
        }

        .main-content table thead th {
            padding: 12px;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #444444;
            border-bottom: 2px solid #ddd;
        }

        .main-content table tbody td {
            padding: 10px;
            font-size: 14px;
            color: #555555;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .main-content table tbody tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }

        /* Botões de ação */
        .main-content table tbody td a button {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .main-content table tbody td a button:hover {
            background-color: #0056b3;
        }

        .main-content table tbody td a button:active {
            background-color: #003d80;
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

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow-y: auto;
            background-color: rgba(0, 0, 0, 0.6);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto 10% auto;
            padding: 30px 40px;
            border-radius: 15px;
            width: 70%;
            max-width: 900px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: fadeInUp 0.4s ease forwards;
        }

        p {
            white-space: normal;
            word-wrap: break-word;
        }


        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 3px solid #2980b9;
            padding-bottom: 8px;
        }

        .modal-content h2,
        .modal-content h3 {
            color: #34495e;
            margin-top: 30px;
            margin-bottom: 15px;
            border-left: 5px solid #2980b9;
            padding-left: 10px;
        }

        .participante-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #f0f8ff, #e6f2ff);
            box-shadow: 0 4px 12px rgba(41, 128, 185, 0.15);
            transition: box-shadow 0.3s ease;
        }

        .participante-card:hover {
            box-shadow: 0 8px 20px rgba(41, 128, 185, 0.3);
        }

        .participante-card p {
            margin: 6px 0;
            font-size: 1rem;
            color: #2c3e50;
        }

        .participante-card p strong::after {

            margin-left: 6px;
            color: #2980b9;
        }

        .modal-content a {
            color: #2980b9;
            text-decoration: none;
            font-weight: 600;
        }

        .modal-content a:hover {
            text-decoration: underline;
        }

        .fechar-modal {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 1.8rem;
            font-weight: bold;
            color: #888;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .fechar-modal:hover {
            color: #2980b9;
        }


        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        button {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
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

            <li>
                <a href="listar.php"><i class="fa fa-list-alt"></i>Projetos submetidos</a> >

            </li>
            <li><a href="avaliados.php"><i class="fa fa-check"></i>Projetos avaliados</a></li>
            <li><a href="/sistema/logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>



    <div class="main-content">
        <h1>Lista de Projetos Submetidos</h1>

        <?php if (!$periodo_aberto): ?>
            <p style="color: red; font-weight: bold; margin-bottom: 20px;">
                Período de submissão encerrado. Não é possível editar nem excluir seu projeto.
            </p>
        <?php endif; ?>


        <table>
            <thead>
                <tr>
                    <th>Título</th> <!-- Cabeçalho da coluna Título -->
                    <th>Categoria</th> <!-- Cabeçalho da coluna Categoria -->
                    <th>Projetor Integrador</th> <!-- Cabeçalho da coluna Participação -->
                    <!--<th>Resumo Arquivo</th>  Cabeçalho da coluna Data Início -->
                    <!--<th>Poster</th>  Cabeçalho da coluna Data Término -->
                    <!-- <th>Link YT</th> Cabeçalho da coluna Editar -->
                    <th>Participantes</th> <!-- Cabeçalho da coluna Participação -->
                    <!--<th>CPF</th> Cabeçalho da coluna Participação -->
                    <!--<th>Email</th> Cabeçalho da coluna Visualizar -->
                    <!--<th>Telefone</th>  Cabeçalho da coluna Título -->
                    <!--<th>Data nascimento</th>  Cabeçalho da coluna Categoria -->
                    <!--<th>Tipo curso</th> Cabeçalho da coluna Participação -->
                    <!--<th>Serie</th>  Cabeçalho da coluna Data Início -->
                    <!--<th>Instituicao</th>  Cabeçalho da coluna Data Término -->
                    <!--<th>Municipio</th>  Cabeçalho da coluna Editar -->
                    <!--<th>Estado</th>  Cabeçalho da coluna Participação -->
                    <!--<th>Dependencia</th>  Cabeçalho da coluna Participação -->
                    <!--<th>Termo de Aceite</th>  Cabeçalho da coluna Visualizar -->
                    <!--<th>Título</th>  Cabeçalho da coluna Título -->
                    <!--<th>Categoria</th>  Cabeçalho da coluna Categoria -->
                    <!--<th>Termo de Aceite</th>  Cabeçalho da coluna Participação -->
                    <th>Orientador</th> <!-- Cabeçalho da coluna Data Início -->
                    <!--<th>CPF Orientador</th>  Cabeçalho da coluna Data Término -->
                    <!--<th>Email Orientador</th>  Cabeçalho da coluna Editar -->
                    <!--<th>Instituicao</th>  Cabeçalho da coluna Participação -->
                    <!--<th>Coorientador nome</th>  Cabeçalho da coluna Participação -->
                    <!--<th>Coorientador CPF</th> Cabeçalho da coluna Participação -->
                    <!--<th>Coorientador Email</th> Cabeçalho da coluna Visualizar -->
                    <th></th> <!--Cabeçalho da coluna Data Início -->
                    <th></th> <!--Cabeçalho da coluna Data Término -->
                    <th></th> <!--Cabeçalho da coluna Editar -->

                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $id = htmlspecialchars($row['id']);
                        echo "<tr>
                <td>" . htmlspecialchars($row['titulo']) . "</td>
                <td>" . htmlspecialchars($row['categoria']) . "</td>
                <td>" . htmlspecialchars($row['projeto_integrador']) . "</td>
                <td>" . htmlspecialchars($row['participantes_nome_completo']) . "</td>
                <td>" . htmlspecialchars($row['nome_orientador']) . "</td>";



                        echo "<td><button onclick=\"abrirModalProjeto($id)\" style=\"cursor: pointer;\">Visualizar</button></td>";

                        if ($periodo_aberto) {
                            echo "<td><a href='editar.php?id=$id'><button>Editar</button></a></td>";
                        } else {
                            echo "<td><button disabled style='background-color: gray; cursor: not-allowed;'>Editar</button></td>";
                        }




                        if ($periodo_aberto) {
                            echo "<td><a href='excluir.php?id=$id' onclick=\"return confirm('Tem certeza que deseja excluir este projeto?');\"><button style='background-color: #dc3545;'>Excluir</button></a></td>";
                        } else {
                            echo "<td><button disabled style='background-color: gray; cursor: not-allowed;'>Excluir</button></td>";
                        }

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Nenhum projeto encontrado.</td></tr>";
                }
                ?>

            </tbody>
        </table>
    </div>
    <div id="modalProjeto" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <div id="conteudoProjeto"></div>
        </div>
    </div>


</body>


</html>


<script>
    function abrirModalProjeto(idProjeto) {
        fetch('../../ajax/get_projeto.php?id=' + idProjeto)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert("Erro: " + data.error);
                    return;
                }

                // Quebra strings separadas em arrays
                const nomes = data.participantes_nome_completo?.split(',') || [];
                const emails = data.participantes_email?.split(',') || [];
                const sexos = data.participantes_sexo?.split(',') || [];
                const identidadesGenero = data.participantes_identidade_genero?.split(',') || [];
                const etnias = data.participantes_etnia?.split(',') || [];
                const telefones = data.participantes_telefone?.split(',') || [];
                const tiposCurso = data.participantes_tipo_curso?.split(',') || [];
                const instituicoes = data.participantes_instituicao?.split(',') || [];
                const municipios = data.participantes_municipio?.split(',') || [];
                const estados = data.participantes_estado?.split(',') || [];
                const dependencia = data.participantes_dependencia?.split(',') || [];
                const nomesSociais = data.participantes_nome_social?.split(',') || [];
                const cpfs = data.participantes_cpf?.split(',') || [];
                const datasNascimento = data.participantes_data_nascimento?.split(',') || [];
                const series = data.participantes_serie?.split(',') || [];


                const orientador = data.nome_orientador || 'N/A';
                const coorientador = data.co_nome || 'Este projeto não possui Coorientador.';
                const resumoFormatado = data.resumo_texto.replace(/\n/g, '<br>');


                let html = `
                <h1>${data.titulo}</h1>
                <p><strong>Área:</strong> ${data.categoria}</p>
                <p><strong>Projeto Integrador:</strong> ${data.projeto_integrador}</p>
                <p><strong>Resumo:</strong> ${resumoFormatado}</p>
                <p><strong>Palavras-chave:</strong> ${data.palavras_chave}</p>
                <p><strong>Link do Vídeo do Projeto:</strong> <a href="${data.link_video}" target="_blank">${data.link_video}</a></p>
                <p><strong>Link do Pôster do Projeto:</strong> <a href="${data.link_poster}" target="_blank">${data.link_poster}</a></p>
                <hr>
                <h2>Participantes</h2>
            `;

                // Montar o bloco para cada participante
                for (let i = 0; i < nomes.length; i++) {
                    html += `
                    <div class="participante-card">
                        <p><strong>Nome:</strong> ${nomes[i] || 'N/A'}</p>
                        <p><strong>Nome Social:</strong> ${nomesSociais[i] || 'N/A'}</p>
                        <p><strong>CPF:</strong> ${cpfs[i] || 'N/A'}</p>
                        <p><strong>Data de Nascimento:</strong> ${datasNascimento[i] || 'N/A'}</p>
                        <p><strong>Série:</strong> ${series[i] || 'N/A'}</p>
                        <p><strong>Email:</strong> ${emails[i] || 'N/A'}</p>
                        <p><strong>Sexo:</strong> ${sexos[i] || 'N/A'}</p>
                        <p><strong>Identidade de gênero:</strong> ${identidadesGenero[i] || 'N/A'}</p>
                        <p><strong>Etnia:</strong> ${etnias[i] || 'N/A'}</p>
                        <p><strong>Telefone:</strong> ${telefones[i] || 'N/A'}</p>
                        <p><strong>Tipo de Curso:</strong> ${tiposCurso[i] || 'N/A'}</p>
                        <p><strong>Instituição:</strong> ${instituicoes[i] || 'N/A'}</p>
                        <p><strong>Município:</strong> ${municipios[i] || 'N/A'}</p>
                        <p><strong>Estado:</strong> ${estados[i] || 'N/A'}</p>
                        <p><strong>Dependência:</strong> ${dependencia[i] || 'N/A'}</p>
                    </div>
                `;
                }

                html += `
                <hr>
                <h3>Orientador</h3>
                <p><strong>Nome do Orientador:</strong> ${orientador}</p>
                <p><strong>Email do Orientador:</strong> ${data.email_orientador}</p>
                <p><strong>Instituição:</strong> ${data.instituicao_orientador}</p>
                <hr>
                <h3>Orientador</h3>
                <p><strong>Nome do Coorientador:</strong> ${coorientador}</p>
                
            `;

                document.getElementById("conteudoProjeto").innerHTML = html;
                document.getElementById("modalProjeto").style.display = "block";
            })
            .catch(error => {
                alert("Erro ao buscar dados do projeto.");
                console.error(error);
            });
    }

    function fecharModal() {
        document.getElementById("modalProjeto").style.display = "none";
    }
</script>

<?php
$conn->close();
?>