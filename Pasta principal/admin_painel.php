<?php
session_start();
require_once 'db.php';

/** * 1. SEGURANÇA: Verifica se o utilizador está logado e se é ADMIN 
 */
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT cargo, nome FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['cargo'] !== 'admin') {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h1>Acesso Negado</h1>
            <p>Apenas membros da comissão (administradores) podem aceder a esta área.</p>
            <a href='index.php'>Voltar para a Loja</a>
         </div>");
}

/** * 2. ESTATÍSTICAS RÁPIDAS (Exemplo de lógica para o painel)
 */
// Total de Vendas Pagas
$totalVendas = $conn->query("SELECT SUM(total) as total FROM vendas WHERE status = 'pago'")->fetch(PDO::FETCH_ASSOC);
// Total de Usuários
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch(PDO::FETCH_ASSOC);
// Produtos Ativos
$totalProdutos = $conn->query("SELECT COUNT(*) as total FROM produtos WHERE ativo = 1")->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel ADM - Turma T6 Medicina</title>
    <style>
        :root {
            --primary: #1a5c37; /* Verde Medicina */
            --secondary: #2c3e50;
            --bg: #f4f7f6;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg);
            margin: 0;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--secondary);
            color: var(--white);
            height: 100vh;
            position: fixed;
            padding: 20px;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.2rem;
            border-bottom: 1px solid #3e5871;
            padding-bottom: 20px;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .sidebar nav ul li {
            margin-bottom: 15px;
        }

        .sidebar nav ul li a {
            color: #bdc3c7;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar nav ul li a:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Conteúdo Principal */
        .main-content {
            margin-left: 290px;
            padding: 40px;
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .welcome-msg {
            font-size: 1.5rem;
            color: var(--secondary);
        }

        /* Cards de Estatísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .card h3 {
            margin: 0;
            font-size: 0.9rem;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .card p {
            margin: 10px 0 0;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary);
        }

        /* Atalhos Rápidos */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-btn {
            background: var(--primary);
            color: white;
            text-decoration: none;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            transition: transform 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-5px);
            background-color: #14462a;
        }

        .logout {
            background-color: #c0392b !important;
        }

    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>COMISSÃO T6</h2>
        <nav>
            <ul>
                <li><a href="admin_painel.php">Dashboard</a></li>
                <li><a href="admin_vendas.php">Vendas/Pedidos</a></li>
                <li><a href="admin_produtos.php">Stock de Produtos</a></li>
                <li><a href="admin_usuarios.php">Lista de Alunos</a></li>
                <li><a href="index.php">Ver Site Público</a></li>
                <li><a href="logout.php" class="logout">Sair</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="header">
            <div class="welcome-msg">
                Olá, <strong><?php echo htmlspecialchars($user['nome']); ?></strong>! 👋
            </div>
            <div class="date">
                <?php echo date('d/m/Y'); ?>
            </div>
        </header>

        <section class="stats-grid">
            <div class="card">
                <h3>Vendas Totais</h3>
                <p>R$ <?php echo number_format($totalVendas['total'] ?? 0, 2, ',', '.'); ?></p>
            </div>
            <div class="card">
                <h3>Utilizadores</h3>
                <p><?php echo $totalUsers['total']; ?></p>
            </div>
            <div class="card">
                <h3>Produtos Ativos</h3>
                <p><?php echo $totalProdutos['total']; ?></p>
            </div>
        </section>

        <h2>Ações Rápidas</h2>
        <section class="quick-actions">
            <a href="admin_produtos_novo.php" class="action-btn">➕ Adicionar Novo Produto</a>
            <a href="admin_vendas.php" class="action-btn">🛒 Gerir Pedidos Pendentes</a>
            <a href="admin_usuarios_aderentes.php" class="action-btn">🎓 Validar Novos Aderentes</a>
        </section>

        <section style="margin-top: 50px; background: white; padding: 20px; border-radius: 10px; box-shadow: var(--shadow);">
            <h3>Dicas de Gestão para a T6</h3>
            <ul>
                <li>Verifica os pagamentos de PIX no banco antes de mudar o status da venda para "Pago".</li>
                <li>Mantém o stock de moletons sempre atualizado para evitar vendas em duplicado.</li>
                <li>A lista de alunos exportada pode ser usada para a entrada em eventos manuais.</li>
            </ul>
        </section>
    </main>

</body>
</html>