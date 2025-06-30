<?php
class Usuario
{
    private $conn; 
    private $table_name = "usuarios"; 
    
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Função para realizar o login
    public function login($email, $senha)
    {
        // Consulta SQL para buscar o usuário pelo e-mail fornecido
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Verifica se o usuário foi encontrado
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC); 

            // Verifica se a senha fornecida corresponde à senha armazenada no banco de dados
            if (password_verify($senha, $user['senha'])) {
                return $user; 
            }
        }
        return false; 
    }

    // Função para criar um novo usuário
    public function criarUsuario($nome, $email, $senha)
    {
        // Verifica se o e-mail já existe no banco de dados
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return "email_existente"; 
        }

        // Caso o e-mail seja único, insere o novo usuário no banco de dados
        $query = "INSERT INTO " . $this->table_name . " (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);

        
        if ($stmt->execute()) {
            return true; 
        }

        return false; 
    }
    public function verificarEmail($email) {
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    
        return $stmt->rowCount() > 0;
    }
    
}
?>
