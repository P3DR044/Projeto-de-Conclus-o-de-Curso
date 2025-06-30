<?php
include_once('../../../config/session.php');
include_once('../../../config/database.php');

// Verifica se é admin
if ($_SESSION['tipo'] !== 'admin') {
  header("Location: ../../login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <title>QR Code para Cadastro de Avaliador</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f5f5;
      color: #333;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .container {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 40px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      text-align: center;
      max-width: 600px;
      width: 100%;
    }

    h2 {
      color: #8B4513;
      margin-bottom: 20px;
    }

    p {
      font-size: 16px;
      margin-bottom: 20px;
    }

    img {
      width: 250px;
      height: 250px;
      object-fit: contain;
      border: 2px solid #8B4513;
      border-radius: 10px;
      padding: 10px;
      background-color: #fff;
    }

    @media (max-width: 600px) {
      .container {
        padding: 20px;
      }

      img {
        width: 200px;
        height: 200px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <h2>QR Code para Cadastro de Avaliador</h2>
    <p>Mostre este QR Code para os avaliadores realizarem seu próprio cadastro:</p>
    <img src="qr_avaliador_img.php" alt="QR Code do link de cadastro" />
  </div>
</body>

</html>