<?php
$host = "localhost";
$user = "root";
$pass = ""; // Por padrão o XAMPP não tem senha no root
$db   = "t6_medicina";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Modo de erros seguro e setagem de acentuação (UTF-8)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8mb4");
} catch(PDOException $e) {
    die("Falha na conexão com o banco de dados: " . $e->getMessage());
}
?>