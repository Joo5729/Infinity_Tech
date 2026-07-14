<?php
// cadastro.php - Tela de formulário de cadastro de novo usuário

if (!isset($_SESSION)) {
    session_start();
}

// Se já logado, redireciona
if (!empty($_SESSION['id'])) {
    header("Location: painel.php");
    exit;
}

$erros = [
    'campos_obrigatorios' => 'Preencha todos os campos obrigatórios.',
    'usuario_existente'   => 'Este nome de usuário já está em uso. Escolha outro.',
    'erro_interno'        => 'Erro interno ao cadastrar. Tente novamente.',
];
$erro_msg = $erros[$_GET['erro'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro · Sistema de Estoque</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0a1628 0%, #0d2856 40%, #1a4a8a 70%, #1565c0 100%);
            position: relative;
            overflow: hidden;
        }

        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
        }
        body::before {
            width: 500px; height: 500px;
            top: -120px; right: -120px;
            background: radial-gradient(circle, rgba(21,101,192,.35) 0%, transparent 70%);
            animation: float1 8s ease-in-out infinite;
        }
        body::after {
            width: 400px; height: 400px;
            bottom: -100px; left: -80px;
            background: radial-gradient(circle, rgba(100,181,246,.25) 0%, transparent 70%);
            animation: float2 10s ease-in-out infinite;
        }
        @keyframes float1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-30px,40px)} }
        @keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(25px,-35px)} }

        .card {
            position: relative;
            width: 100%;
            max-width: 440px;
            background: rgba(255,255,255,.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 20px;
            padding: 48px 40px 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.1);
            animation: slideUp .5s ease both;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0);    }
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 64px; height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #1e88e5, #42a5f5);
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(30,136,229,.4);
        }
        .logo svg { width: 32px; height: 32px; fill: #fff; }

        .card h1 {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }
        .subtitulo {
            text-align: center;
            font-size: .88rem;
            color: rgba(255,255,255,.55);
            margin-top: 4px;
            margin-bottom: 32px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .alerta {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alerta.erro { background: rgba(239,83,80,.18); border: 1px solid rgba(239,83,80,.4); color: #ef9a9a; }
        .alerta svg  { width: 18px; height: 18px; fill: currentColor; flex-shrink: 0; }

        .campo { margin-bottom: 20px; }

        label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: rgba(255,255,255,.7);
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input-wrap { position: relative; }

        .input-wrap > svg {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            width: 18px; height: 18px;
            fill: rgba(255,255,255,.35);
            pointer-events: none;
            transition: fill .2s;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            outline: none;
            transition: border-color .25s, background .25s, box-shadow .25s;
        }
        input::placeholder { color: rgba(255,255,255,.3); }
        input:focus {
            border-color: #42a5f5;
            background: rgba(66,165,245,.1);
            box-shadow: 0 0 0 3px rgba(66,165,245,.2);
        }
        .input-wrap:focus-within > svg { fill: #42a5f5; }

        /* indicador de força de senha */
        .forca-wrap { margin-top: 8px; display: flex; gap: 4px; }
        .forca-barra { height: 4px; flex: 1; border-radius: 4px; background: rgba(255,255,255,.1); transition: background .3s; }
        .forca-texto { font-size: .75rem; color: rgba(255,255,255,.45); margin-top: 4px; }

        .toggle-senha {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        .toggle-senha svg { width: 18px; height: 18px; fill: rgba(255,255,255,.35); transition: fill .2s; }
        .toggle-senha:hover svg { fill: #42a5f5; }

        .btn-cadastrar {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #1565c0, #1e88e5);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .5px;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(21,101,192,.45);
            transition: transform .15s, box-shadow .15s, filter .15s;
        }
        .btn-cadastrar:hover  { filter: brightness(1.1); box-shadow: 0 8px 24px rgba(21,101,192,.6); transform: translateY(-1px); }
        .btn-cadastrar:active { transform: translateY(0); }

        .divisor { border: none; border-top: 1px solid rgba(255,255,255,.1); margin: 28px 0; }

        .rodape { text-align: center; font-size: .875rem; color: rgba(255,255,255,.5); }
        .rodape a { color: #64b5f6; text-decoration: none; font-weight: 600; transition: color .2s; }
        .rodape a:hover { color: #90caf9; text-decoration: underline; }

        /* requisitos de senha */
        .requisitos { margin-top: 8px; }
        .req { font-size: .78rem; color: rgba(255,255,255,.4); display: flex; align-items: center; gap: 6px; margin-bottom: 3px; transition: color .2s; }
        .req.ok { color: #81c784; }
        .req svg { width: 12px; height: 12px; fill: currentColor; }
    </style>
</head>
<body>
<div class="card">

    <div class="logo">
        <svg viewBox="0 0 24 24"><path d="M15 14c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm0-2a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm-9 2c-.29 0-.62.02-.97.05C6.19 14.89 7 16.6 7 18.5V20H1v-2c0-2.66 5.33-4 5-4z"/></svg>
    </div>

    <h1>Criar Conta</h1>
    <p class="subtitulo">Sistema de Estoque</p>

    <?php if (!empty($erro_msg)): ?>
        <div class="alerta erro">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
            <?= htmlspecialchars($erro_msg) ?>
        </div>
    <?php endif; ?>

    <form action="../controller/cadastrar.php" method="POST" autocomplete="off" novalidate>

        <div class="campo">
            <label for="nome">Nome completo</label>
            <div class="input-wrap">
                <input type="text" id="nome" name="nome" placeholder="Seu nome" required
                       value="<?= htmlspecialchars($_GET['nome'] ?? '') ?>">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
            </div>
        </div>

        <div class="campo">
            <label for="usuario">Nome de usuário</label>
            <div class="input-wrap">
                <input type="text" id="usuario" name="usuario" placeholder="ex: joao.silva" required
                       value="<?= htmlspecialchars($_GET['usuario'] ?? '') ?>">
                <svg viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 1 0 10A5 5 0 0 1 12 2zm0 12c5.33 0 8 2.67 8 4v2H4v-2c0-1.33 2.67-4 8-4z"/></svg>
            </div>
        </div>

        <div class="campo">
            <label for="senha">Senha</label>
            <div class="input-wrap">
                <input type="password" id="senha" name="senha" placeholder="••••••••" required
                       oninput="verificarSenha(this.value)">
                <svg viewBox="0 0 24 24"><path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10a2 2 0 0 0-2-2zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm3.1-9H8.9V6a3.1 3.1 0 1 1 6.2 0v2z"/></svg>
                <button type="button" class="toggle-senha" onclick="toggleSenha()" title="Mostrar/ocultar senha">
                    <svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zm0 12.5a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
                </button>
            </div>
            <div class="forca-wrap">
                <div class="forca-barra" id="b1"></div>
                <div class="forca-barra" id="b2"></div>
                <div class="forca-barra" id="b3"></div>
                <div class="forca-barra" id="b4"></div>
            </div>
            <p class="forca-texto" id="forca-texto"></p>
            <div class="requisitos" id="requisitos">
                <p class="req" id="r-len"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> Mínimo 8 caracteres</p>
                <p class="req" id="r-num"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> Contém número</p>
                <p class="req" id="r-esp"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> Contém caractere especial</p>
            </div>
        </div>

        <button type="submit" class="btn-cadastrar">Criar Conta</button>
        <div class="campo">
            <label style="color: rgba(255,255,255,.7); font-size: .82rem; font-weight: 600; text-transform: uppercase; margin-bottom: 8px; display: block;">Tipo de Perfil</label>
            <div style="display: flex; gap: 20px; padding: 10px 0;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #fff; font-size: 1rem; text-transform: none;">
                    <input type="radio" name="tipo" value="cliente" checked style="width: 18px; height: 18px; cursor: pointer;"> 
                    Sou Cliente
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #fff; font-size: 1rem; text-transform: none;">
                    <input type="radio" name="tipo" value="funcionario" style="width: 18px; height: 18px; cursor: pointer;"> 
                    Sou Funcionário
                </label>
            </div>
        </div>
    </form>

    <hr class="divisor">
    <p class="rodape">Já tem uma conta? <a href="../index.php">Fazer login</a></p>
</div>

<script>
function toggleSenha() {
    const inp = document.getElementById('senha');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}

function verificarSenha(v) {
    const barras  = [b1,b2,b3,b4].map(id => document.getElementById(id));
    const cores   = ['#ef5350','#ffa726','#66bb6a','#42a5f5'];
    const labels  = ['Muito fraca','Fraca','Boa','Forte'];

    const temLen  = v.length >= 8;
    const temNum  = /\d/.test(v);
    const temEsp  = /[^a-zA-Z0-9]/.test(v);
    const temMai  = /[A-Z]/.test(v);

    document.getElementById('r-len').className = 'req' + (temLen ? ' ok' : '');
    document.getElementById('r-num').className = 'req' + (temNum ? ' ok' : '');
    document.getElementById('r-esp').className = 'req' + (temEsp ? ' ok' : '');

    let forca = [temLen, temNum, temEsp, temMai].filter(Boolean).length;
    if (v.length === 0) forca = 0;

    const barIds = ['b1','b2','b3','b4'];
    barIds.forEach((id, i) => {
        document.getElementById(id).style.background =
            i < forca ? cores[forca - 1] : 'rgba(255,255,255,.1)';
    });

    document.getElementById('forca-texto').textContent =
        v.length > 0 ? labels[forca - 1] || '' : '';
}
</script>
</body>
</html>
