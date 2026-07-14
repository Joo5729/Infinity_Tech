<?php
// mvc/painel.php - Área do Funcionário (Gestão e Orçamentos)

if (!isset($_SESSION)) session_start();

if (empty($_SESSION['id']) || $_SESSION['tipo'] !== 'funcionario') {
    header("Location: index.php");
    exit;
}

include __DIR__ . '/model/conexao.php';

$nomeUsuario = $_SESSION['nome'] ?? 'Funcionário';
$mensagem    = "";
$tipo_msg    = "";

// ── Ação: EXCLUIR PEÇA ────────────────────────────────────────
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE idProdutos = :id");
        $stmt->execute([':id' => $id]);
        $mensagem = "Peça/Material excluído com sucesso.";
        $tipo_msg = "sucesso";
    } catch (PDOException $e) {
        $mensagem = "Erro ao excluir: " . $e->getMessage();
        $tipo_msg = "erro";
    }
}

// Buscar as peças cadastradas
$stmt = $pdo->query("SELECT * FROM produtos ORDER BY idProdutos DESC");
$lista = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Funcionário · Infinity Tech AC</title>
    <style>
        /* Reset e Base */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0a1628 0%, #0d2856 40%, #1a4a8a 70%, #1565c0 100%);
            background-attachment: fixed;
            color: #fff;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Efeitos de fundo combinando com o sistema */
        body::before, body::after {
            content: ''; position: fixed; border-radius: 50%; pointer-events: none; z-index: 0;
        }
        body::before {
            width: 500px; height: 500px; top: -120px; left: -120px;
            background: radial-gradient(circle, rgba(21,101,192,.15) 0%, transparent 70%);
        }

        /* Menu Lateral (Sidebar) */
        .sidebar {
            width: 260px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
            z-index: 10;
        }

        .logo {
            display: flex; align-items: center; gap: 12px;
            font-size: 1.2rem; font-weight: 700; color: #fff;
            margin-bottom: 50px; padding-left: 10px;
        }
        .logo svg { width: 28px; height: 28px; fill: #42a5f5; }

        .menu-links { list-style: none; display: flex; flex-direction: column; gap: 10px; flex: 1; }
        
        .menu-links a {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; color: rgba(255, 255, 255, 0.7);
            padding: 12px 16px; border-radius: 10px;
            font-weight: 600; font-size: 0.95rem;
            transition: all 0.3s;
        }
        .menu-links a:hover, .menu-links a.ativo {
            background: rgba(66, 165, 245, 0.15);
            color: #fff;
        }
        .menu-links a svg { width: 20px; height: 20px; fill: currentColor; }

        .btn-sair-lateral {
            margin-top: auto; display: flex; align-items: center; gap: 12px;
            text-decoration: none; color: #ef5350;
            padding: 12px 16px; border-radius: 10px;
            font-weight: 600; transition: all 0.3s;
        }
        .btn-sair-lateral:hover { background: rgba(239, 83, 80, 0.1); }

        /* Conteúdo Principal */
        .conteudo {
            flex: 1; padding: 40px 50px; z-index: 10;
            display: flex; flex-direction: column; gap: 30px;
        }

        .header-conteudo { display: flex; justify-content: space-between; align-items: flex-end; }
        .header-conteudo h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: 8px; }
        .header-conteudo p { color: rgba(255, 255, 255, 0.6); font-size: 1.05rem; }
        
        .perfil-topo {
            background: rgba(255, 255, 255, 0.08); padding: 10px 20px;
            border-radius: 30px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Botão Ação Principal (Criar Orçamento) */
        .acao-destaque {
            background: linear-gradient(135deg, #42a5f5, #1565c0);
            padding: 25px; border-radius: 16px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .acao-destaque h2 { font-size: 1.5rem; margin-bottom: 5px; }
        .acao-destaque p { color: rgba(255,255,255,0.8); }
        .btn-criar {
            background: #fff; color: #1565c0; padding: 15px 30px;
            border-radius: 30px; text-decoration: none; font-weight: bold;
            font-size: 1.1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .btn-criar:hover { transform: translateY(-3px); }

        /* Alertas */
        .alerta { padding: 15px 20px; border-radius: 8px; font-weight: 600; margin-bottom: 10px; }
        .alerta.sucesso { background: rgba(102, 187, 106, 0.2); color: #81c784; border: 1px solid rgba(102, 187, 106, 0.3); }
        .alerta.erro { background: rgba(239, 83, 80, 0.2); color: #e57373; border: 1px solid rgba(239, 83, 80, 0.3); }

        /* Box da Tabela */
        .box-tabela {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header-tabela { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-tabela h3 { font-size: 1.3rem; font-weight: 600; }
        .btn-nova-peca { background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 8px; font-size: 0.9rem; transition: background 0.3s; }
        .btn-nova-peca:hover { background: rgba(255,255,255,0.2); }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        th { color: rgba(255,255,255,0.5); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover td { background: rgba(255,255,255,0.02); }
        tr:last-child td { border-bottom: none; }
        
        .acoes a { text-decoration: none; font-size: 0.85rem; font-weight: bold; padding: 6px 12px; border-radius: 6px; margin-right: 5px; }
        .btn-editar { background: rgba(66, 165, 245, 0.1); color: #64b5f6; }
        .btn-editar:hover { background: rgba(66, 165, 245, 0.2); }
        .btn-excluir { background: rgba(239, 83, 80, 0.1); color: #e57373; }
        .btn-excluir:hover { background: rgba(239, 83, 80, 0.2); }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo">
            <svg viewBox="0 0 24 24"><path d="M21 8.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2.5M21 8.5H3M21 8.5V20a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8.5m6 4h6"/></svg>
            Infinity Tech
        </div>

        <ul class="menu-links">
            <li>
                <a href="painel.php" class="ativo">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Painel Geral
                </a>
            </li>
            <li>
                <a href="criar_orcamento.php">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    Novo Orçamento
                </a>
            </li>
        </ul>

        <a href="logout.php" class="btn-sair-lateral">
            <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:currentColor;"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            Sair do Sistema
        </a>
    </aside>

    <main class="conteudo">
        
        <div class="header-conteudo">
            <div>
                <h1>Gestão de Serviços</h1>
                <p>Administração de orçamentos e controle de estoque de peças.</p>
            </div>
            <div class="perfil-topo">
                Técnico(a) <?= htmlspecialchars($nomeUsuario) ?> 🛠️
            </div>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta <?= $tipo_msg ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <div class="acao-destaque">
            <div>
                <h2>Gerador de Orçamentos e PDF</h2>
                <p>Gere orçamentos detalhados com taxa de serviço, deslocação e peças necessárias, enviando direto com a chave PIX.</p>
            </div>
            <a href="criar_orcamento.php" class="btn-criar">➕ Criar Novo Orçamento</a>
        </div>

        <div class="box-tabela">
            <div class="header-tabela">
                <h3>Estoque de Peças e Materiais</h3>
                <a href="#" class="btn-nova-peca">+ Cadastrar Peça</a>
            </div>
            
            <?php if (count($lista) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Cód</th>
                        <th>Peça / Material</th>
                        <th>Modelo / Descrição</th>
                        <th>Marca</th>
                        <th>Preço (R$)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lista as $p): ?>
                    <tr>
                        <td style="color: rgba(255,255,255,0.4);">#<?= (int) $p['idProdutos'] ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($p['Nome_produto'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($p['Modelo'] ?? '—') ?></td>
                        <td><span style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;"><?= htmlspecialchars($p['Marca'] ?? '—') ?></span></td>
                        <td style="color: #66bb6a; font-weight: bold;">R$ <?= number_format((float)$p['Preco'], 2, ',', '.') ?></td>
                        <td class="acoes">
                            <a href="#" class="btn-editar">Editar</a>
                            <a href="painel.php?acao=excluir&id=<?= (int)$p['idProdutos'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta peça?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p style="color: rgba(255,255,255,0.5); text-align: center; padding: 20px;">Nenhuma peça cadastrada no momento.</p>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>