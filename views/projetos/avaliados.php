<?php
include_once('../../config/session.php');

verificarLogin();

$servername = "localhost";
$username = "u915057572_Admin";
$password = "F3cc1f2022";
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
    GROUP_CONCAT(DISTINCT participantes.nome_completo) AS participantes_nome_completo,
    GROUP_CONCAT(DISTINCT participantes.cpf) AS participantes_cpf,
    GROUP_CONCAT(DISTINCT participantes.email) AS participantes_email,
    GROUP_CONCAT(DISTINCT participantes.telefone) AS participantes_telefone,
    GROUP_CONCAT(DISTINCT participantes.data_nascimento) AS participantes_data_nascimento,
    GROUP_CONCAT(DISTINCT participantes.tipo_curso) AS participantes_tipo_curso,
    GROUP_CONCAT(DISTINCT participantes.serie) AS participantes_serie,
    GROUP_CONCAT(DISTINCT participantes.instituicao) AS participantes_instituicao,
    GROUP_CONCAT(DISTINCT participantes.municipio) AS participantes_municipio,
    GROUP_CONCAT(DISTINCT participantes.estado) AS participantes_estado,
    GROUP_CONCAT(DISTINCT participantes.dependencia) AS participantes_dependencia,
    GROUP_CONCAT(DISTINCT participantes.termo) AS participantes_termo,

    orientadores.nome_orientador,
    orientadores.cpf_orientador,
    orientadores.email_orientador,
    orientadores.instituicao_orientador,
    orientadores.co_nome,
    orientadores.co_cpf,
    orientadores.co_email,

    COUNT(DISTINCT a.id) AS qtd_avaliacoes,

    MAX(a.criatividade) AS criatividade,
    MAX(a.metodo_cientifico) AS metodo_cientifico,
    MAX(a.profundidade) AS profundidade,
    MAX(a.aplicabilidade) AS aplicabilidade,
    MAX(a.apresentacao) AS apresentacao,
    MAX(a.carater_integrador) AS carater_integrador,
    MAX(a.observacoes) AS observacoes,

    MAX(u.nome) AS nome_avaliador

FROM projetos

LEFT JOIN participantes ON projetos.id = participantes.id_projeto

LEFT JOIN orientadores ON orientadores.id_projeto = projetos.id

LEFT JOIN avaliacoes a ON projetos.id = a.projeto_id AND a.id IS NOT NULL

LEFT JOIN usuarios u ON a.avaliador_id = u.id

WHERE projetos.usuario_id = ?

GROUP BY projetos.id, 
         orientadores.nome_orientador, 
         orientadores.cpf_orientador,
         orientadores.email_orientador,
         orientadores.instituicao_orientador,
         orientadores.co_nome,
         orientadores.co_cpf,
         orientadores.co_email
";



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
    <title>Projetos Avaliados</title>
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
            text-align: left;
            font-size: 14px;
            font-weight: bold;
            color: #444444;
            border-bottom: 2px solid #ddd;
            text-align: center;
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

        .status-box {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.9rem;
            pointer-events: none;
            cursor: default;
            color: white;
            text-align: center;
            text-transform: capitalize;
        }

        .status-aprovado {
            background-color: #28a745;
        }

        .status-reprovado {
            background-color: #dc3545;
        }

        .status-pendente {
            background-color: #ffc107;
            color: black;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .modal-content {
            background-color: #fff;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            width: 60%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .status-alteracoes {
            background-color: #f39c12;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .ver-alteracoes-btn {
            margin-left: 10px;
            padding: 5px 10px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            margin-left: 15px;
            vertical-align: middle;
        }
    </style>
</head>

<body>


    <div class="spot">
        <ul>
            <li><a href="../home.php"><i class="fa fa-home"></i>Página Inicial</a></li>
            <li><a href="anteriores.php"><i class="fa fa-folder-open"></i>Projetos separados por categoria</a></li>
            <li><a href="criar.php"><i class="fa fa-upload"></i>Submeter um projeto</a></li>

            <li>
                <a href="listar.php"><i class="fa fa-list-alt"></i>Projetos submetidos</a>

            </li>
            <li><a href="avaliados.php"><i class="fa fa-check"></i>Projetos avaliados</a></li>
            <li><a href="/Projeto_TCC/logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Meus Projetos Avaliados</h1>
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
                    <th>Situação do Projeto</th> <!--Cabeçalho da coluna Data Início -->
                    <th></th> <!--Cabeçalho da coluna Data Término -->
                    <th></th> <!--Cabeçalho da coluna Editar -->

                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $status = htmlspecialchars($row['status']);
                        $class = '';
                        switch (strtolower($status)) {
                            case 'aprovado':
                                $class = 'status-aprovado';
                                break;
                            case 'reprovado':
                                $class = 'status-reprovado';
                                break;
                            case 'alteracoes':
                                $class = 'status-alteracoes';
                                break;
                            case 'pendente':
                            default:
                                $class = 'status-pendente';
                                break;
                        }

                        echo "<tr>
                    <td>" . htmlspecialchars($row['titulo']) . "</td>
                    <td>" . htmlspecialchars($row['categoria']) . "</td>
                    <td>" . htmlspecialchars($row['projeto_integrador']) . "</td>
                    <td>" . htmlspecialchars($row['participantes_nome_completo']) . "</td>
                    <td>" . htmlspecialchars($row['nome_orientador']) . "</td>
                    <td><span class='status-box $class'>" . ucfirst($status) . "</span></td>";


                        echo "<td>";




                        if (strtolower($status) === 'alteracoes') {
                            $justificativaJson = htmlspecialchars(json_encode($row['comentario'], JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
                            echo "<button class='btn-ver-justificativa' data-justificativa=\"$justificativaJson\">Ver Alterações Solicitadas</button>";
                        } elseif (
                            strtolower($status) === 'aprovado' &&
                            (int)$row['qtd_avaliacoes'] > 0 &&
                            !empty($row['criatividade'])
                        ) {
                            // Botão: Visualizar Avaliação (somente se houver avaliação)
                            $dataAval = htmlspecialchars(json_encode([
                                'criatividade' => $row['criatividade'],
                                'metodo_cientifico' => $row['metodo_cientifico'],
                                'profundidade' => $row['profundidade'],
                                'aplicabilidade' => $row['aplicabilidade'],
                                'apresentacao' => $row['apresentacao'],
                                'carater_integrador' => $row['carater_integrador'],
                                'observacoes' => $row['observacoes'],
                                'nome_avaliador' => $row['nome_avaliador'],
                            ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE));

                            echo "<button class='btn-ver-avaliacao' data-avaliacao='$dataAval'>Visualizar Avaliação</button>";
                        }

                        echo "</td>";


                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Nenhum projeto encontrado.</td></tr>";
                }
                ?>



            </tbody>
        </table>
        <div id="justificativaModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Solicitações de Alterações</h2>
                <p id="justificativaTexto"></p>
            </div>
        </div>
        <div id="avaliacaoModal" class="modal" style="display:none;">
            <div class="modal-content" style="max-width:600px; margin:auto; background:#fff; padding:20px; border-radius:8px; position:relative;">
                <span id="closeAval" style="position:absolute; top:10px; right:20px; cursor:pointer; font-size:24px;">&times;</span>
                <h2>Avaliação do Projeto</h2>
                <table style="width:100%; border-collapse: collapse;">
                    <p><strong>Avaliador:</strong> <span id="nome_avaliador"></span></p>
                    <tr>
                        <th>Critério</th>
                        <th>Nota</th>
                    </tr>
                    <tr>
                        <td>Criatividade</td>
                        <td id="criatividade"></td>
                    </tr>
                    <tr>
                        <td>Método Científico</td>
                        <td id="metodo_cientifico"></td>
                    </tr>
                    <tr>
                        <td>Profundidade</td>
                        <td id="profundidade"></td>
                    </tr>
                    <tr>
                        <td>Aplicabilidade</td>
                        <td id="aplicabilidade"></td>
                    </tr>
                    <tr>
                        <td>Apresentação</td>
                        <td id="apresentacao"></td>
                    </tr>
                    <tr>
                        <td>Caráter Integrador</td>
                        <td id="carater_integrador"></td>
                    </tr>
                </table>
                <h3>Observações</h3>
                <p id="observacoes" style="white-space: pre-wrap;"></p>
            </div>
        </div>
    </div>

</body>


</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // MODAL de Alterações
        const modalAlteracoes = document.getElementById('justificativaModal');
        const textoJustificativa = document.getElementById('justificativaTexto');
        const btnFecharAlteracoes = modalAlteracoes.querySelector('.close');

        document.querySelectorAll('.btn-ver-justificativa').forEach(btn => {
            btn.addEventListener('click', function() {
                const justificativa = this.getAttribute('data-justificativa');
                textoJustificativa.textContent = justificativa || 'Nenhuma justificativa disponível.';
                modalAlteracoes.style.display = 'block';
            });
        });

        btnFecharAlteracoes.addEventListener('click', function() {
            modalAlteracoes.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === modalAlteracoes) {
                modalAlteracoes.style.display = 'none';
            }
        });

        // MODAL de Avaliação
        const modalAvaliacao = document.getElementById('avaliacaoModal');
        const btnFecharAvaliacao = document.getElementById('closeAval');

        document.querySelectorAll('.btn-ver-avaliacao').forEach(button => {
            button.addEventListener('click', () => {
                const avaliacao = JSON.parse(button.getAttribute('data-avaliacao'));
                document.getElementById('criatividade').textContent = avaliacao.criatividade;
                document.getElementById('metodo_cientifico').textContent = avaliacao.metodo_cientifico;
                document.getElementById('profundidade').textContent = avaliacao.profundidade;
                document.getElementById('aplicabilidade').textContent = avaliacao.aplicabilidade;
                document.getElementById('apresentacao').textContent = avaliacao.apresentacao;
                document.getElementById('carater_integrador').textContent = avaliacao.carater_integrador;
                document.getElementById('observacoes').textContent = avaliacao.observacoes;
                document.getElementById('nome_avaliador').textContent = avaliacao.nome_avaliador;

                modalAvaliacao.style.display = 'block';
            });
        });

        btnFecharAvaliacao.addEventListener('click', () => {
            modalAvaliacao.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === modalAvaliacao) {
                modalAvaliacao.style.display = 'none';
            }
        });
    });
</script>

<style>
    <?php
    $conn->close();
    ?>