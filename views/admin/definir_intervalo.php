<?php
include_once('../../config/session.php');
include_once('../../config/database.php');

if ($_SESSION['tipo'] !== 'admin') {
  header("Location: ../../login.php");
  exit();
}

$database = new Database();
$conn = $database->getConnection();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data_inicio = $_POST['data_inicio'] ?? '';
  $data_fim = $_POST['data_fim'] ?? '';

  if (!$data_inicio || !$data_fim) {
    $msg = "Por favor, preencha ambas as datas.";
  } elseif ($data_fim < $data_inicio) {
    $msg = "Data fim deve ser maior ou igual à data início.";
  } else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM configuracoes_submissao");
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
      // Atualiza
      $stmt = $conn->prepare("UPDATE configuracoes_submissao SET data_inicio = :data_inicio, data_fim = :data_fim");
      $stmt->execute([
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim,
      ]);
      $msg = "Datas atualizadas com sucesso.";
    } else {
      // Insere
      $stmt = $conn->prepare("INSERT INTO configuracoes_submissao (data_inicio, data_fim) VALUES (:data_inicio, :data_fim)");
      $stmt->execute([
        ':data_inicio' => $data_inicio,
        ':data_fim' => $data_fim,
      ]);
      $msg = "Datas salvas com sucesso.";
    }
  }
}

$stmt = $conn->prepare("SELECT data_inicio, data_fim FROM configuracoes_submissao LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <title>Configurar Período de Submissão</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f5f7;
      padding: 20px;
    }

    .container {
      max-width: 400px;
      margin: auto;
      background: white;
      padding: 20px;
      border-radius: 6px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      color: #333;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      color: #555;
    }

    input[type="date"] {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }

    button {
      margin-top: 20px;
      width: 100%;
      background-color: #007bff;
      border: none;
      color: white;
      padding: 10px;
      font-size: 16px;
      border-radius: 4px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    .msg {
      margin-top: 15px;
      text-align: center;
      color: green;
    }

    .error {
      color: red;
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>Configurar Período de Submissão</h2>

    <?php if ($msg): ?>
      <p class="msg <?= strpos($msg, 'sucesso') === false ? 'error' : '' ?>"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="data_inicio">Data Início:</label>
      <input type="date" id="data_inicio" name="data_inicio" required value="<?= htmlspecialchars($config['data_inicio'] ?? '') ?>">

      <label for="data_fim">Data Fim:</label>
      <input type="date" id="data_fim" name="data_fim" required value="<?= htmlspecialchars($config['data_fim'] ?? '') ?>">

      <button type="submit">Salvar</button>
    </form>
  </div>
</body>

</html>