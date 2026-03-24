<?php
session_start();
require_once 'db.php';

// Define o header como JSON para respostas da API
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'total') {
    // Busca o total arrecadado das doações confirmadas
    try {
        $stmt = $conn->query("SELECT SUM(valor) as total FROM doacoes WHERE status = 'confirmado'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $row['total'] ? $row['total'] : 0;
        echo json_encode(['sucesso' => true, 'arrecadado' => $total]);
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'erro' => 'Erro ao buscar dados do banco']);
    }
} elseif ($action === 'doar') {
    // Registra uma nova doação com status pendente
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $valor = isset($_POST['valor']) ? floatval($_POST['valor']) : 0;
        $mensagem = $_POST['mensagem'] ?? '';
        
        // Se houver sistema de login, pega o ID, caso contrário deixa NULL
        $usuario_id = $_SESSION['usuario_id'] ?? null;

        if ($valor <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'Valor inválido']);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO doacoes (usuario_id, valor, mensagem, status) VALUES (:usuario_id, :valor, :mensagem, 'pendente')");
            $stmt->execute([
                'usuario_id' => $usuario_id,
                'valor' => $valor,
                'mensagem' => $mensagem
            ]);
            echo json_encode(['sucesso' => true, 'id' => $conn->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['sucesso' => false, 'erro' => 'Falha ao gravar no banco de dados']);
        }
    } else {
        echo json_encode(['sucesso' => false, 'erro' => 'Método inválido. Use POST.']);
    }
} else {
    echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida']);
}
