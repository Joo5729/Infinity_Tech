<?php
if (!isset($_SESSION)) session_start();
if (empty($_SESSION['id']) || $_SESSION['tipo'] !== 'funcionario') {
    die("Acesso negado.");
}

require __DIR__ . '/model/conexao.php';
// Inclua o autoload do Dompdf
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente      = (int) $_POST['id_cliente'];
    $diagnostico     = $_POST['diagnostico'];
    $taxa_deslocacao = (float) $_POST['taxa_deslocacao'];
    $taxa_servico    = (float) $_POST['taxa_servico'];
    
    $produtos_post   = $_POST['produtos'] ?? [];
    $quantidades     = $_POST['quantidades'] ?? [];

    // Obter dados do cliente
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$id_cliente]);
    $cliente = $stmt->fetch();
    $nome_cliente = $cliente ? htmlspecialchars($cliente['nome']) : 'Cliente Genérico';

    $itens_html = "";
    $total_pecas = 0;
    $itens_bd = [];

    // Processar os produtos selecionados
    for ($i = 0; $i < count($produtos_post); $i++) {
        if (!empty($produtos_post[$i])) {
            list($id_prod, $preco) = explode('|', $produtos_post[$i]);
            $qtd = (int) $quantidades[$i];
            $subtotal = $preco * $qtd;
            $total_pecas += $subtotal;
            
            // Obter nome da peça
            $st = $pdo->prepare("SELECT Nome_produto FROM produtos WHERE idProdutos = ?");
            $st->execute([$id_prod]);
            $nome_peca = $st->fetchColumn();

            // Formatando valores
            $preco_fmt = number_format((float)$preco, 2, ',', '.');
            $subtotal_fmt = number_format((float)$subtotal, 2, ',', '.');

            $itens_html .= "
            <tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$nome_peca}</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: center;'>{$qtd}</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>R$ {$preco_fmt}</td>
                <td style='padding: 8px; border-bottom: 1px solid #ddd; text-align: right;'>R$ {$subtotal_fmt}</td>
            </tr>";

            $itens_bd[] = ['id' => $id_prod, 'qtd' => $qtd, 'preco' => $preco];
        }
    }

    $valor_total = $taxa_deslocacao + $taxa_servico + $total_pecas;

    // 1. Guardar o Orçamento na Base de Dados
    $pdo->beginTransaction();
    try {
        $stmt_orc = $pdo->prepare("INSERT INTO orcamentos (id_cliente, diagnostico, taxa_deslocacao, taxa_servico, valor_total) VALUES (?, ?, ?, ?, ?)");
        $stmt_orc->execute([$id_cliente, $diagnostico, $taxa_deslocacao, $taxa_servico, $valor_total]);
        $id_orcamento = $pdo->lastInsertId();

        $stmt_item = $pdo->prepare("INSERT INTO orcamento_itens (id_orcamento, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        foreach ($itens_bd as $item) {
            $stmt_item->execute([$id_orcamento, $item['id'], $item['qtd'], $item['preco']]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao salvar orçamento no banco de dados: " . $e->getMessage());
    }

    // 2. Desenhar o Layout do PDF (HTML seguro com buffer)
    ob_start(); 
    ?>
    <div style="font-family: sans-serif; color: #333;">
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 style="color: #1565c0; margin: 0;">INFINITY TECH</h1>
            <p style="margin: 5px 0;">Climatização e Ar-Condicionado</p>
            <p style="margin: 0; font-size: 12px; color: #777;">Contato: (11) 99999-9999 | contato@infinitytech.com.br</p>
        </div>

        <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h3 style="margin-top: 0;">Orçamento Nº #<?= str_pad($id_orcamento, 5, '0', STR_PAD_LEFT) ?></h3>
            <p><strong>Cliente:</strong> <?= $nome_cliente ?></p>
            <p><strong>Data:</strong> <?= date('d/m/Y') ?></p>
        </div>

        <h4 style="color: #1565c0; border-bottom: 2px solid #1565c0; padding-bottom: 5px;">Diagnóstico Técnico</h4>
        <p style="background: #eef5fb; padding: 10px; border-left: 4px solid #1565c0;"><?= nl2br(htmlspecialchars($diagnostico)) ?></p>

        <h4 style="color: #1565c0; border-bottom: 2px solid #1565c0; padding-bottom: 5px; margin-top: 30px;">Custos e Materiais</h4>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: #1565c0; color: white;">
                    <th style="padding: 10px; text-align: left;">Descrição</th>
                    <th style="padding: 10px; text-align: center;">Qtd</th>
                    <th style="padding: 10px; text-align: right;">Unitário</th>
                    <th style="padding: 10px; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?= $itens_html ?>
                <tr>
                    <td colspan="3" style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">Taxa de Visita / Deslocação:</td>
                    <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">R$ <?= number_format($taxa_deslocacao, 2, ',', '.') ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">Mão de Obra (Serviço):</td>
                    <td style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">R$ <?= number_format($taxa_servico, 2, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: right; margin-top: 20px; font-size: 20px;">
            <strong>TOTAL A PAGAR: <span style="color: #ef5350;">R$ <?= number_format($valor_total, 2, ',', '.') ?></span></strong>
        </div>

        <div style="margin-top: 50px; text-align: center; border: 2px dashed #1e88e5; padding: 20px; border-radius: 10px; background: #fdfdfd;">
            <h3 style="margin-top: 0; color: #1e88e5;">Informações para Pagamento via Pix</h3>
            <p><strong>Chave Pix (CNPJ/Telefone/Email):</strong> 12.345.678/0001-99</p>
            <p><strong>Nome do Favorecido:</strong> Infinity Tech Ar-Condicionado</p>
            <p style="font-size: 12px; color: #777;">Após o pagamento, envie o comprovante para aprovarmos a execução do serviço.</p>
        </div>
    </div>
    <?php
    // Empacota o HTML gerado em uma string garantida
    $html = ob_get_clean();

    // 3. Inicializar o DOMPDF e gerar o ficheiro
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica'); // Evita bugs com fontes
    $dompdf = new Dompdf($options);
    
    // Agora o $html é 100% garantido que é uma string!
    $dompdf->loadHtml((string)$html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Mostrar o PDF diretamente no navegador
    $dompdf->stream("Orcamento_" . $id_orcamento . ".pdf", ["Attachment" => false]);
    exit;
} else {
    echo "Acesso inválido. Volte e utilize o formulário.";
}
?>