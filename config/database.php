<?php
// Definindo a classe 'Database', responsável por estabelecer a conexão com o banco de dados
class Database
{
    
    private $host = "localhost"; 
    private $db_name = "u915057572_projeto_tcc"; 
    private $username = "u915057572_Admin"; 
    private $password = "F3cc1f2022@"; 
    private $conn; // Variável que armazenará a conexão com o banco de dados

    public function getConnection()
    {
        
        $this->conn = null;

        try {
            
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
           
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            
            echo "Erro de conexão: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>

