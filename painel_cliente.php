<?php
// mvc/painel_cliente.php - Área exclusiva do Cliente

if (!isset($_SESSION)) session_start();

// Controle de Acesso: Apenas Clientes
if (empty($_SESSION['id']) || $_SESSION['tipo'] !== 'cliente') {
    header("Location: index.php");
    exit;
}

$nomeUsuario = $_SESSION['nome'] ?? 'Cliente';

// Aqui futuramente entrarão as consultas reais no Banco de Dados
$total_orcamentos = 0; 
$orcamentos_aprovados = 0;
$orcamentos_pendentes = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Cliente · Infinity Tech</title>
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

        /* Efeitos de fundo combinando com o Login */
        body::before, body::after {
            content: ''; position: fixed; border-radius: 50%; pointer-events: none; z-index: 0;
        }
        body::before {
            width: 500px; height: 500px; top: -120px; left: -120px;
            background: radial-gradient(circle, rgba(21,101,192,.15) 0%, transparent 70%);
        }
        body::after {
            width: 400px; height: 400px; bottom: -100px; right: -80px;
            background: radial-gradient(circle, rgba(100,181,246,.1) 0%, transparent 70%);
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
            border: 1px solid transparent;
        }
        .btn-sair-lateral:hover {
            background: rgba(239, 83, 80, 0.1); border-color: rgba(239, 83, 80, 0.3);
        }

        /* Conteúdo Principal */
        .conteudo {
            flex: 1; padding: 40px 50px; z-index: 10;
            display: flex; flex-direction: column; gap: 30px;
        }

        .header-conteudo { display: flex; justify-content: space-between; align-items: flex-end; }
        .header-conteudo h1 { font-size: 2rem; font-weight: 700; margin-bottom: 8px; }
        .header-conteudo p { color: rgba(255, 255, 255, 0.6); font-size: 1.05rem; }
        
        .perfil-topo {
            background: rgba(255, 255, 255, 0.08); padding: 10px 20px;
            border-radius: 30px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Cards de Dashboard */
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px; padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s, background 0.3s;
        }
        .card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.08); }
        .card h3 { font-size: 0.9rem; color: rgba(255,255,255,0.6); text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; }
        .card .valor { font-size: 2.5rem; font-weight: 700; }
        
        .c-azul { color: #42a5f5; }
        .c-verde { color: #66bb6a; }
        .c-laranja { color: #ffa726; }

        /* Tabela de Orçamentos */
        .box-tabela {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            flex: 1;
        }
        .box-tabela h2 { font-size: 1.3rem; margin-bottom: 20px; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        th { color: rgba(255,255,255,0.5); font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:hover td { background: rgba(255,255,255,0.02); }
        tr:last-child td { border-bottom: none; }
        
        .vazio { text-align: center; padding: 40px !important; color: rgba(255,255,255,0.4); font-style: italic; }
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
                <a href="painel_cliente.php" class="ativo">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                    Meus Orçamentos
                </a>
            </li>
            <li>
                <a href="#">
                    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    Meu Perfil
                </a>
            </li>
        </ul>

        <a href="logout.php" class="btn-sair-lateral">
            <svg viewBox="0 0 24 24" style="width:20px; height:20px; fill:currentColor;"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
            Sair da Conta
        </a>
    </aside>

    <main class="conteudo">
        
        <div class="header-conteudo">
            <div>
                <h1>Área do Cliente</h1>
                <p>Acompanhe suas solicitações e orçamentos em tempo real.</p>
            </div>
            <div class="perfil-topo">
                Olá, <?= htmlspecialchars($nomeUsuario) ?> 👋
            </div>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Orçamentos Solicitados</h3>
                <div class="valor c-azul"><?= $total_orcamentos ?></div>
            </div>
            <div class="card">
                <h3>Aprovados</h3>
                <div class="valor c-verde"><?= $orcamentos_aprovados ?></div>
            </div>
            <div class="card">
                <h3>Em Análise (Pendentes)</h3>
                <div class="valor c-laranja"><?= $orcamentos_pendentes ?></div>
            </div>
        </div>

        <div class="box-tabela">
            <h2>Histórico Recente</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nº do Orçamento</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4" class="vazio">Você ainda não possui orçamentos registrados.</td>
                    </tr>
                    
                    </tbody>
            </table>
        </div>

    </main>

</body>
</html>