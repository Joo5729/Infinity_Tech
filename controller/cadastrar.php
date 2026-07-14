<?php
// mvc/controller/cadastrar.php - Cadastro de usuário 
require __DIR__ . '/../model/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/cadastro.php');
    exit();
}

$nome    = trim($_POST['nome']    ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$senha   = $_POST['senha'] ?? '';

// Captura a escolha do formulário. Se tentar burlar, vira cliente por padrão.
$tipo_escolhido = $_POST['tipo'] ?? 'cliente';
$tipo = ($tipo_escolhido === 'funcionario') ? 'funcionario' : 'cliente';

if (empty($nome) || empty($usuario) || empty($senha)) {
    header('Location: ../view/cadastro.php?erro=campos_obrigatorios');
    exit();
}

try {
    // Verifica se o usuário já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);

    if ($stmt->fetch()) {
        header('Location: ../view/cadastro.php?erro=usuario_existente');
        exit();
    }

    // Criptografa a senha
    $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

    // Insere no banco incluindo a coluna 'tipo'
    $sql = "INSERT INTO usuarios (nome, usuario, senha, tipo) VALUES (:nome, :usuario, :senha, :tipo)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome'    => $nome,
        ':usuario' => $usuario,
        ':senha'   => $senha_hash,
        ':tipo'    => $tipo,
    ]);

    header('Location: ../index.php?cadastro=sucesso');
    exit();

} catch (PDOException $e) {
    header('Location: ../view/cadastro.php?erro=erro_interno');
    exit();
}