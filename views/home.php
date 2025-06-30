<?php
include_once('../config/session.php');


include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Busca os projetos submetidos
$query = "SELECT id, titulo, resumo_texto, data_submissao, link_video, palavras_chave FROM projetos WHERE status = 'aprovado' ORDER BY data_submissao DESC LIMIT 6";
$stmt = $db->prepare($query);
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera os dados do usuário logado da sessão
$nome_usuario = $_SESSION['nome_usuario'] ?? 'Usuário não identificado';
$id_usuario = $_SESSION['usuario_id'] ?? 'ID não encontrado';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- FontAwesome -->
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

            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
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

        .projetos-container {
            display: inline-grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            width: auto;
            height: auto;
            margin-top: 20px;
        }

        .projeto {
            position: relative;
            cursor: pointer;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            padding: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 500px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .projeto:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .projeto h3 {
            margin-top: 0;
            font-size: 1.6rem;
            color: #222;
            margin-bottom: 12px;
            max-width: 80%;
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            overflow: hidden;
            box-sizing: border-box;
        }

        .thumbnail-link {
            display: block;
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease;
        }

        .thumbnail-link:hover {
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
        }

        .thumbnail-link img {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            display: block;
            filter: brightness(0.9);
            transition: filter 0.3s ease;
        }

        .thumbnail-link:hover img {
            filter: brightness(1);
        }

        .youtube-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0.9;
            transition: opacity 0.3s ease;
            filter: drop-shadow(0 0 6px rgba(0, 0, 0, 0.7));
            width: 80px;
            height: 80px;
        }

        .thumbnail-link:hover .youtube-icon {
            opacity: 1;
            filter: drop-shadow(0 0 10px rgba(255, 0, 0, 0.85));
        }

        .resumo {
            font-size: 1rem;
            color: #444;
            line-height: 1.5;
            margin-bottom: 10px;
            min-height: 60px;
            max-width: 100%;
            word-wrap: break-word;
            white-space: normal;
            overflow-wrap: break-word;
            overflow: hidden;
            box-sizing: border-box;
        }

        .btn-like {
            border: none;
            background: none;
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 28px;
            color: gray;
            transition: color 0.3s ease;
            display: flex;
            align-items: center;
        }

        .btn-like[aria-pressed="true"] {
            color: #e50914;
        }

        .btn-like:hover {
            color: #e50914;
        }

        .likes-count {
            font-size: 16px;
            color: #222;
            margin-left: 6px;
            user-select: none;
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
            <li><a href="home.php"><i class="fa fa-home"></i>Página Inicial</a></li>
            <li><a href="../views/projetos/anteriores.php"><i class="fa fa-folder-open"></i>Projetos separados por categoria</a></li>
            <li><a href="../views/projetos/criar.php"><i class="fa fa-upload"></i>Submeter um projeto</a></li>

            <li>
                <a href="../views/projetos/listar.php"><i class="fa fa-list-alt"></i>Projetos submetidos</a>
            </li>
            <li><a href="../views/projetos/avaliados.php"><i class="fa fa-check"></i>Projetos avaliados</a></li>
            <li><a href="../logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Página Inicial</h1>
        <p><strong>Usuário logado:</strong> <?= htmlspecialchars($nome_usuario) ?> </p>
        <p>Bem-vindo! Use o menu lateral para navegar pelo sistema.</p>

        <h2>Projetos Avaliados</h2>
        <div class="projetos-container">

            <?php foreach ($projetos as $projeto):

                $projeto_id = $projeto['id'];
                $usuario_id = $_SESSION['usuario_id'] ?? null;
                $curtiu = false;
                $totalLikes = 0;

                // Buscar se usuário curtiu
                if ($usuario_id) {
                    $stmt = $db->prepare("SELECT 1 FROM projeto_likes WHERE projeto_id = ? AND usuario_id = ?");
                    $stmt->execute([$projeto_id, $usuario_id]);
                    $curtiu = $stmt->fetch() ? true : false;
                }

                // Total de likes
                $stmt = $db->prepare("SELECT COUNT(*) FROM projeto_likes WHERE projeto_id = ?");
                $stmt->execute([$projeto_id]);
                $totalLikes = $stmt->fetchColumn();
            ?>

                <div class="projeto" onclick="abrirModalProjeto(<?= $projeto_id ?>)">
                    <h3><?= htmlspecialchars($projeto['titulo']) ?></h3>

                    <?php
                    if (!function_exists('extrairYoutubeID')) {
                        function extrairYoutubeID($url)
                        {
                            parse_str(parse_url($url, PHP_URL_QUERY), $vars);
                            return $vars['v'] ?? null;
                        }
                    }
                    $video_id = extrairYoutubeID($projeto['link_video']);
                    ?>

                    <a href="<?= htmlspecialchars($projeto['link_video']) ?>" target="_blank" onclick="event.stopPropagation();" class="thumbnail-link" title="Assistir vídeo no YouTube">
                        <img
                            src="https://img.youtube.com/vi/<?= $video_id ?>/maxresdefault.jpg"
                            alt="Capa do Vídeo"
                            onerror="this.onerror=null; this.src='https://img.youtube.com/vi/<?= $video_id ?>/hqdefault.jpg';" />
                        <svg class="youtube-icon" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" stroke="#FF0000" stroke-width="2"></circle>
                            <polygon points="9 7 16 12 9 17 9 7" fill="#fff"></polygon>
                        </svg>
                    </a>

                    <p class="resumo"><?= nl2br(htmlspecialchars($projeto['resumo_texto'])) ?></p>

                    <p class="palavras-chave">
                        <strong>Palavras-chave:</strong> <?= htmlspecialchars($projeto['palavras_chave']) ?>
                    </p>

                    <button
                        class="btn-like"
                        data-projeto-id="<?= $projeto_id ?>"
                        data-logado="<?= $usuario_id ? '1' : '0' ?>"
                        aria-pressed="<?= $curtiu ? 'true' : 'false' ?>"
                        title="<?= $curtiu ? 'Remover like' : 'Dar like' ?>"
                        onclick="event.stopPropagation(); likeProjeto(this);">
                        &#10084;
                        <span class="likes-count"><?= $totalLikes ?></span>
                    </button>
                </div>



            <?php endforeach; ?>
        </div>
    </div>
    <div id="modalProjeto" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fecharModal()">&times;</span>
            <div id="conteudoProjeto"></div>
        </div>
    </div>

    </div>

</body>

</html>
<script>
    function likeProjeto(button) {
        const logado = button.dataset.logado === '1';
        const projetoId = button.dataset.projetoId;
        const liked = button.getAttribute('aria-pressed') === 'true';

        if (!logado) {
            alert("Você precisa estar logado para curtir projetos.");
            return;
        }

        fetch('like_projeto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    projeto_id: projetoId,
                    like: !liked
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualiza botão visualmente
                    button.setAttribute('aria-pressed', (!liked).toString());
                    button.style.color = !liked ? 'red' : 'gray';
                    button.querySelector('.likes-count').textContent = data.totalLikes;
                } else {
                    alert(data.message || 'Erro ao curtir projeto.');
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                alert('Erro ao conectar com o servidor.');
            });
    }

    function abrirModalProjeto(idProjeto) {
        fetch('../ajax/get_projeto.php?id=' + idProjeto)
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

                const orientador = data.nome_orientador || 'N/A';
                const coorientador = data.co_nome || 'Este projeto não possui Coorientador.';

                let html = `
                <h1>${data.titulo}</h1>
                <p><strong>Área:</strong> ${data.categoria}</p>
                <p><strong>Projeto Integrador:</strong> ${data.projeto_integrador}</p>
                <p><strong>Resumo:</strong> ${data.resumo_texto}</p>
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
                <h3>Coorientador</h3>
                <p>${coorientador}</p>
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