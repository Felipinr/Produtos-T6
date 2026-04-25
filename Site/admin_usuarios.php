<?php
session_start();
require_once 'db.php';

/** * 1. SEGURANÇA: Verifica se o utilizador está logado e se é ADMIN 
 */
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT cargo FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$me || $me['cargo'] !== 'admin') {
    die("Acesso Negado.");
}

/** * 2. LÓGICA DE PROMOÇÃO/REBAIXAMENTO DE CARGO
 * Apenas um admin pode alterar o cargo de outro usuário
 */
if (isset($_GET['alterar_cargo']) && isset($_GET['id'])) {
    $novoCargo = $_GET['alterar_cargo'] === 'admin' ? 'admin' : 'usuario';
    $targetId = $_GET['id'];

    // Impede que o admin se rebaixe a si próprio para não perder acesso ao painel acidentalmente
    if ($targetId == $_SESSION['usuario_id']) {
        $erro = "Você não pode alterar seu próprio cargo.";
    } else {
        $update = $conn->prepare("UPDATE usuarios SET cargo = :cargo WHERE id = :id");
        $update->execute(['cargo' => $novoCargo, 'id' => $targetId]);
        header("Location: admin_usuarios.php?sucesso=Cargo atualizado");
        exit;
    }
}

/** * 3. BUSCA DE USUÁRIOS
 */
$query = "SELECT id, nome, email, cpf, turma, tipo, cargo FROM usuarios ORDER BY nome ASC";
$usuarios = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - T6 Medicina</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #2c3e50; color: white; height: 100vh; padding: 20px; position: fixed; }
        .main { margin-left: 290px; padding: 40px; width: 100%; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #1a5c37; color: white; }
        .btn { padding: 8px 12px; text-decoration: none; border-radius: 4px; font-size: 0.85rem; color: white; }
        .btn-promote { background: #27ae60; }
        .btn-demote { background: #e67e22; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge-admin { background: #1a5c37; color: white; }
        .badge-user { background: #bdc3c7; color: #2c3e50; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .alert-success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>Painel T6</h2>
        <a href="admin_painel.php" style="color:white; text-decoration:none;">← Voltar ao Dashboard</a>
    </aside>

    <main class="main">
        <h1>Gestão de Alunos e Acessos</h1>

        <?php if (isset($erro)): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['sucesso']); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF / Turma</th>
                    <th>Tipo</th>
                    <th>Cargo Atual</th>
                    <th>Ações de Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($u['nome']); ?></strong><br>
                        <small><?php echo htmlspecialchars($u['email']); ?></small>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($u['cpf']); ?><br>
                        <small>Turma: <?php echo htmlspecialchars($u['turma']); ?></small>
                    </td>
                    <td><?php echo ucfirst($u['tipo']); ?></td>
                    <td>
                        <span class="badge <?php echo $u['cargo'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                            <?php echo strtoupper($u['cargo']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <?php if ($u['cargo'] === 'admin'): ?>
                                <a href="admin_usuarios.php?id=<?php echo $u['id']; ?>&alterar_cargo=usuario" class="btn btn-demote" onclick="return confirm('Remover privilégios de Admin deste usuário?')">Remover Admin</a>
                            <?php else: ?>
                                <a href="admin_usuarios.php?id=<?php echo $u['id']; ?>&alterar_cargo=admin" class="btn btn-promote" onclick="return confirm('Tornar este usuário um Administrador da Comissão?')">Tornar Admin</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <small style="color: #999;">(Você)</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

</body>
</html>