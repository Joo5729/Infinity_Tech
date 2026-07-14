<?php
// mvc/model/conexao.php
// Conexão com o banco de dados — Infinity Tech Sistema de Estoque

$host   = 'localhost';
$dbname = 'infinity_tech';
$user   = 'root';
$pass   = '';           // XAMPP/Laragon: deixe vazio. WAMP/MAMP: verifique sua senha.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Erro de conexão com o banco de dados: ' . $e->getMessage());
}
