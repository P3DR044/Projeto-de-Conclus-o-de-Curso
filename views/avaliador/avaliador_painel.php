<?php
include_once('../../config/session.php');
include_once('../../config/database.php');

if (!in_array($_SESSION['tipo'], ['avaliador', 'admin'])) {
    header('Location: ../../login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Trata ações de avaliação e exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_projeto'])) {
    $id = $_POST['id_projeto'];
    $status = $_POST['status'] ?? null;
    $comentario = $_POST['comentario'] ?? null;

    // Exclusão (apenas admin)
    if (isset($_POST['excluir']) && $_POST['excluir'] == '1' && $_SESSION['tipo'] === 'admin') {
        // Apaga avaliações relacionadas
        $stmtDelAvaliacoes = $db->prepare("DELETE FROM avaliacoes WHERE projeto_id = :projeto_id");
        $stmtDelAvaliacoes->execute([':projeto_id' => $id]);

        // Apaga o projeto
        $stmtDelProjeto = $db->prepare("DELETE FROM projetos WHERE id = :id");
        $stmtDelProjeto->execute([':id' => $id]);

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // Validação do status e permissão (avaliador ou admin)
    if (in_array($status, ['aprovado', 'reprovado', 'pendente', 'alteracoes']) && in_array($_SESSION['tipo'], ['avaliador', 'admin'])) {
        // Verifica se avaliação já existe para este avaliador e projeto
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM avaliacoes WHERE projeto_id = :projeto_id AND avaliador_id = :avaliador_id");
        $stmtCheck->execute([
            ':projeto_id' => $id,
            ':avaliador_id' => $_SESSION['usuario_id']
        ]);

        if ($stmtCheck->fetchColumn() == 0) {
            // Insere avaliação nova
            $stmtInsert = $db->prepare("
                INSERT INTO avaliacoes (projeto_id, avaliador_id, status, comentario)
                VALUES (:projeto_id, :avaliador_id, :status, :comentario)
            ");
            $stmtInsert->execute([
                ':projeto_id' => $id,
                ':avaliador_id' => $_SESSION['usuario_id'],
                ':status' => $status,
                ':comentario' => $comentario
            ]);
        } else {
            // Atualiza avaliação existente
            $stmtUpdate = $db->prepare("
                UPDATE avaliacoes
                SET status = :status, comentario = :comentario
                WHERE projeto_id = :projeto_id AND avaliador_id = :avaliador_id
            ");
            $stmtUpdate->execute([
                ':status' => $status,
                ':comentario' => $comentario,
                ':projeto_id' => $id,
                ':avaliador_id' => $_SESSION['usuario_id']
            ]);
        }

        // Atualiza status do projeto somente se for admin
        if ($_SESSION['tipo'] === 'admin') {
            $stmtUpdateProjeto = $db->prepare("UPDATE projetos SET status = :status WHERE id = :id");
            $stmtUpdateProjeto->execute([
                ':status' => $status,
                ':id' => $id
            ]);
        }
    }
}

// Busca projetos para exibir conforme o tipo do usuário
if ($_SESSION['tipo'] === 'admin') {
    // Admin vê todos os projetos
    $query = "SELECT id, titulo, resumo_texto, data_submissao, status 
              FROM projetos 
              ORDER BY data_submissao DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Avaliador vê projetos aprovados nas áreas que pode avaliar e que ainda não avaliou
    $stmtAreas = $db->prepare("SELECT categoria, quantidade FROM avaliador_areas WHERE avaliador_id = :avaliador_id");
    $stmtAreas->execute([':avaliador_id' => $_SESSION['usuario_id']]);
    $areas = $stmtAreas->fetchAll(PDO::FETCH_ASSOC);

    $projetos = [];

    foreach ($areas as $area) {
        $categoria = $area['categoria'];
        $quantidade = (int)$area['quantidade'];

        if ($quantidade > 0) {
            $query = "
                SELECT p.id, p.titulo, p.resumo_texto, p.data_submissao, p.status
                FROM projetos p
                WHERE p.categoria = :categoria
                  AND p.status = 'aprovado'
                  AND NOT EXISTS (
                      SELECT 1 FROM avaliacoes a
                      WHERE a.projeto_id = p.id
                        AND a.avaliador_id = :avaliador_id
                  )
                ORDER BY p.data_submissao DESC
                LIMIT :quantidade
            ";

            $stmt = $db->prepare($query);
            $stmt->bindValue(':categoria', $categoria, PDO::PARAM_STR);
            $stmt->bindValue(':avaliador_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
            $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
            $stmt->execute();

            $projetosCategoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $projetos = array_merge($projetos, $projetosCategoria);
        }
    }
}
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Avaliador</title>
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

        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        button {
            padding: 10px 20px;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #808080;
        }

        .reprovar-btn {
            background-color: red;
            color: white;

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

        .visu {
            background-color: blue;
            width: 120px;
            display: block;
            margin: 0px auto;
            text-align: center;

        }

        .exclui {
            background-color: black;
        }

        .penden {
            background-color: purple;
        }

        .desc {
            text-align: center;
            width: 50%;
            max-width: 400px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            box-sizing: border-box;
        }

        .botoes_ava {
            text-align: center;
        }

        .acoes-form button,
        .botoes_ava a button {
            width: 120px;
            display: block;
            margin: 10px auto;
            text-align: center;
        }

        .acoes-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .botoes_ava a {
            text-decoration: none;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 60%;
            position: relative;
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

        .solicitar-alteracoes {
            background-color: #f39c12;
            color: white;
        }

        .botaoconf {
            width: 100% !important;
            text-align: center !important;
            white-space: normal;
            display: block;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .btnSolicitarAlteracoes {
            background-color: #f39c12;
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
    </style>
</head>

<body>
    <div class="spot">
        <ul>
            <li><a href="../home.php"><i class="fa fa-home"></i>Página Inicial</a></li>
            <li><a href="../../logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Painel do Avaliador</h1>
        <p>Bem-vindo, <?= htmlspecialchars($_SESSION['nome_usuario']) ?>!</p>

        <div class="table-container">
            <h2>Projetos para avaliação:</h2>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Resumo</th>
                        <th>Ações</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projetos)): ?>
                        <tr>
                            <td colspan="4">Nenhum projeto disponível para avaliação.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projetos as $projeto): ?>
                            <tr>
                                <td><?= htmlspecialchars($projeto['titulo']) ?></td>
                                <td class="desc"><?= nl2br(htmlspecialchars($projeto['resumo_texto'])) ?></td>
                                <td class="botoes_ava">
                                    <button class="visu" onclick="abrirModalProjeto(<?= $projeto['id'] ?>)">Visualizar</button>
                                    <?php if ($_SESSION['tipo'] === 'avaliador' && $projeto['status'] === 'aprovado'): ?>
                                        <button type="button" onclick="abrirModalAvaliacao(<?= $projeto['id'] ?>, '<?= htmlspecialchars($_SESSION['nome_usuario'], ENT_QUOTES) ?>')">Avaliar</button>
                                    <?php elseif ($_SESSION['tipo'] !== 'avaliador'): ?>
                                        <!-- Para admin, mantém os botões originais -->
                                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja executar esta ação?');" class="acoes-form" style="display:inline;">
                                            <input type="hidden" name="id_projeto" value="<?= $projeto['id'] ?>">
                                            <button type="button" class="btnSolicitarAlteracoes">Solicitar Alterações</button>
                                            <button type="submit" name="status" value="aprovado">Aprovar</button>
                                            <button type="submit" name="status" value="reprovado" class="reprovar-btn">Reprovar</button>
                                            <button type="submit" name="status" value="pendente" class="penden">Deixar Pendente</button>
                                            <button type="submit" name="excluir" value="1" class="exclui">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($projeto['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para justificar alterações -->
    <div id="modalAlteracoes" class="modal">
        <div class="modal-content">
            <span id="closeModal" class="close">&times;</span>
            <h2>Justificativa para alterações</h2>
            <form id="formAlteracoes" method="POST">
                <input type="hidden" name="id_projeto" id="modalProjetoId" value="">
                <textarea name="comentario" rows="5" cols="50" required placeholder="Digite a justificativa aqui..."></textarea><br><br>
                <button type="submit" name="status" value="alteracoes">Enviar Solicitação</button>
            </form>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- Modal para visualizar projeto -->
    <div id="modalProjeto" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <div id="conteudoProjeto">Carregando...</div>
        </div>
        <div class="modal-backdrop"></div>
    </div>
    <div id="modalAvaliacao" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="fecharModalAvaliacao()">&times;</span>
            <h2>Avaliar Projeto</h2>
            <form id="formAvaliacao" method="POST" action="avaliar_projeto.php">
                <input type="hidden" name="id_projeto" id="avaliacaoProjetoId" value="">
                <label>Nome completo do avaliador:</label><br>
                <input type="text" name="nome_avaliador" id="nomeAvaliador" readonly><br><br>

                <label>Criatividade e Inovação:</label><br>
                <select name="criatividade" required>
                    <option value="">Selecione</option>
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Uso de Método Científico ou Engenharia:</label><br>
                <select name="metodo_cientifico" required>
                    <option value="">Selecione</option>
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Profundidade da pesquisa:</label><br>
                <select name="profundidade" required>
                    <option value="">Selecione</option>
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Aplicabilidade dos resultados no cotidiano da sociedade:</label><br>
                <select name="aplicabilidade" required>
                    <option value="">Selecione</option>
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Apresentação e desenvoltura na apresentação do trabalho:</label><br>
                <select name="apresentacao" required>
                    <option value="">Selecione</option>
                    <?php for ($i = 0; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Caráter integrador do projeto:</label><br>
                <select name="carater_integrador" required>
                    <option value="">Selecione</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select><br><br>

                <label>Observações:</label><br>
                <textarea name="observacoes" rows="4" cols="50" placeholder="Digite suas observações aqui..."></textarea><br><br>

                <button type="submit">Enviar Avaliação</button>
            </form>
        </div>
        <div class="modal-backdrop"></div>
    </div>
    <script>
        function abrirModalAvaliacao(idProjeto, nomeAvaliador) {
            document.getElementById('avaliacaoProjetoId').value = idProjeto;
            document.getElementById('nomeAvaliador').value = nomeAvaliador;
            document.getElementById('modalAvaliacao').style.display = 'block';
        }

        function fecharModalAvaliacao() {
            document.getElementById('modalAvaliacao').style.display = 'none';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('modalAvaliacao');
            if (event.target === modal) {
                fecharModalAvaliacao();
            }
        }
        // Abre modal de justificativa de alterações
        window.onload = function() {
            const modal = document.getElementById('modalAlteracoes');
            const closeBtn = document.getElementById('closeModal');
            const modalProjetoId = document.getElementById('modalProjetoId');

            document.querySelectorAll('.btnSolicitarAlteracoes').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const form = btn.closest('form');
                    const idProjeto = form.querySelector('input[name="id_projeto"]').value;
                    modalProjetoId.value = idProjeto;
                    modal.style.display = 'block';
                });
            });

            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            modal.querySelector('.modal-backdrop').addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        // Modal visualizar projeto
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

        document.querySelectorAll('.modal').forEach(modal => {
            modal.querySelector('.modal-backdrop').addEventListener('click', () => {
                modal.style.display = 'none';
            });
        });
    </script>
</body>

</html>