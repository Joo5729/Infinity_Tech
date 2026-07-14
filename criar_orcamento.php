<?php
// mvc/criar_orcamento.php - Área Administrativa
if (!isset($_SESSION)) session_start();

if (empty($_SESSION['id']) || $_SESSION['tipo'] !== 'funcionario') {
    header("Location: index.php");
    exit;
}

require __DIR__ . '/model/conexao.php';

// Obter clientes para o formulário
$clientes = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'cliente' ORDER BY nome ASC")->fetchAll();
// Obter produtos (peças de ar-condicionado)
$produtos = $pdo->query("SELECT idProdutos, Nome_produto, Preco FROM produtos ORDER BY Nome_produto ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Orçamento · Infinity Tech AC</title>
    <style>
        /* Estilos baseados no seu layout moderno */
        body { font-family: 'Segoe UI', sans-serif; background: #0a1628; color: #fff; padding: 40px; }
        .container { max-width: 800px; margin: 0 auto; background: rgba(255,255,255,0.05); padding: 30px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); }
        h2 { color: #42a5f5; margin-bottom: 20px; }
        .campo { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: rgba(255,255,255,0.7); }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.2); color: #fff; margin-bottom: 10px; }
        .linha-produto { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .btn { background: #42a5f5; color: #fff; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #1e88e5; }
        .btn-add { background: #66bb6a; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Criar Novo Orçamento de Ar-Condicionado</h2>
        
        <form action="gerar_pdf.php" method="POST" target="_blank">
            <div class="campo">
                <label>Cliente</label>
                <select name="id_cliente" required>
                    <option value="">Selecione o Cliente...</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label>Diagnóstico / Problema Relatado</label>
                <textarea name="diagnostico" rows="4" required placeholder="Ex: Ar-condicionado pingando água, necessita higienização e troca de isolamento..."></textarea>
            </div>

            <div style="display: flex; gap: 20px;">
                <div class="campo" style="flex: 1;">
                    <label>Taxa de Deslocação / Visita (R$)</label>
                    <input type="number" step="0.01" name="taxa_deslocacao" value="50.00" required>
                </div>
                <div class="campo" style="flex: 1;">
                    <label>Valor da Mão de Obra (R$)</label>
                    <input type="number" step="0.01" name="taxa_servico" value="150.00" required>
                </div>
            </div>

            <hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">
            <h3>Peças e Materiais</h3>
            
            <div id="lista-produtos">
                <div class="linha-produto">
                    <select name="produtos[]" style="flex: 3;">
                        <option value="">Sem peças adicionais</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['idProdutos'] ?>|<?= $p['Preco'] ?>"><?= htmlspecialchars($p['Nome_produto']) ?> - R$ <?= number_format($p['Preco'], 2, ',', '.') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantidades[]" value="1" min="1" style="flex: 1;" placeholder="Qtd">
                </div>
            </div>
            
            <button type="button" class="btn btn-add" onclick="adicionarPeca()">+ Adicionar Peça</button>
            <br><br><hr style="border-color: rgba(255,255,255,0.1); margin: 20px 0;">

            <button type="submit" class="btn">Salvar e Gerar PDF</button>
            <a href="painel.php" style="color: #ef5350; margin-left: 15px; text-decoration: none;">Cancelar</a>
        </form>
    </div>

    <script>
        // Função para adicionar mais campos de peças dinamicamente
        function adicionarPeca() {
            const container = document.getElementById('lista-produtos');
            const linha = document.querySelector('.linha-produto').cloneNode(true);
            container.appendChild(linha);
        }
    </script>
</body>
</html>