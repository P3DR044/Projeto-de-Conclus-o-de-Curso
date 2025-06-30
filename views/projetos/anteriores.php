<?php
include_once('../../config/database.php');

function gerarSlug($titulo)
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
    return $slug;
}


$database = new Database();
$db = $database->getConnection();

$query = "
   SELECT 
    p.id, 
    p.imagem_projeto,
    p.titulo, 
    p.descricao,
    p.status,
    p.categoria,
    p.slug, 
    u.nome AS nome_avaliador
FROM 
    projetos p
LEFT JOIN 
    usuarios u ON p.avaliador_id = u.id
WHERE 
    p.status IN ('')
ORDER BY 
    p.categoria, p.titulo;
";

$stmt = $db->prepare($query);
$stmt->execute();
$projetos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$projetosPorCategoria = [];
foreach ($projetos as $projeto) {
    $categoria = $projeto['categoria'];
    if (!isset($projetosPorCategoria[$categoria])) {
        $projetosPorCategoria[$categoria] = [];
    }
    $projetosPorCategoria[$categoria][] = $projeto;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projetos</title>
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
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .projetos-container {
            margin-top: 20px;
        }

        .projeto {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }

        .projeto p {
            text-align: justify;
        }

        .projeto h3 {
            margin: 0 0 10px;
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

        .imagem-projeto {
            max-width: 100%;
            max-height: 200px;
            display: block;
            margin-bottom: 10px;
            border-radius: 5px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="spot">
        <ul>
            <li><a href="../home.php"><i class="fa fa-home"></i>Página Inicial</a></li>
            <li><a href="#"><i class="fa fa-folder-open"></i>Projetos separados por categoria</a></li>
            <li><a href="criar.php"><i class="fa fa-upload"></i>Submeter um projeto</a></li>

            <li><a href="listar.php"><i class="fa fa-list-alt"></i>Projetos submetidos</a></li>
            <li><a href="avaliados.php"><i class="fa fa-check"></i>Projetos avaliados</a></li>
            <li><a href="/sistema/logout.php" id="closeButton"><i class="fa fa-sign-out"></i>Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Projetos Avaliados por Categoria</h1>
        <?php if (!empty($projetosPorCategoria)): ?>
            <?php foreach ($projetosPorCategoria as $categoria => $projetos): ?>
                <div class="categoria">
                    <h2><?= htmlspecialchars($categoria) ?></h2>
                    <?php foreach ($projetos as $projeto): ?>
                        <div class="projeto">
                            <h3><?= htmlspecialchars($projeto['titulo']) ?></h3>
                            <p><?= nl2br(htmlspecialchars($projeto['descricao'])) ?></p>
                            <p><strong>Avaliador:</strong> <?= htmlspecialchars($projeto['nome_avaliador']) ?></p>
                            <a href="projeto/<?= htmlspecialchars($projeto['slug']) ?>"
                                style="display:inline-block;margin-top:10px;padding:8px 12px;background-color:#CD853F;color:white;text-decoration:none;border-radius:4px;">
                                Visualizar
                            </a>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nenhum projeto avaliado encontrado.</p>
        <?php endif; ?>
    </div>
</body>

</html>