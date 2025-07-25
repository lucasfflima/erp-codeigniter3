<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="card">
    <div class="card-header">
        <h4>Pedido #<?= $pedido->id ?></h4>
    </div>
    <div class="card-body">
        <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($pedido->created_at)) ?></p>

        <?php if (!empty($pedido->cupom)) : ?>
            <p><strong>Cupom aplicado:</strong> <?= htmlspecialchars($pedido->cupom->codigo) ?> (<?= (float)$pedido->cupom->valor ?><?= $pedido->cupom->tipo === 'percentual' ? '%' : ' R$' ?>)</p>
        <?php endif; ?>

        <h5>Itens:</h5>

        <?php if (!empty($pedido->itens)) : ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Preço Unitário</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $subtotal = 0;
                    foreach ($pedido->itens as $item):
                        $precoUnitario = $item->produto->preco ?? 0;
                        $totalItem = $precoUnitario * $item->quantidade;
                        $subtotal += $totalItem;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($item->produto->nome ?? 'Produto removido') ?></td>
                            <td>R$ <?= number_format($precoUnitario, 2, ',', '.') ?></td>
                            <td><?= (int)$item->quantidade ?></td>
                            <td>R$ <?= number_format($totalItem, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
                $desconto = 0;
                if (!empty($pedido->cupom)) {
                    $desconto = $pedido->cupom->tipo === 'percentual'
                        ? ($subtotal * $pedido->cupom->valor / 100)
                        : $pedido->cupom->valor;
                }
                $totalFinal = max(0, $subtotal - $desconto);
            ?>

            <h5>
                Subtotal: R$ <?= number_format($subtotal, 2, ',', '.') ?><br>
                <?php if ($desconto > 0): ?>
                    Desconto: -R$ <?= number_format($desconto, 2, ',', '.') ?><br>
                <?php endif; ?>
                <strong>Total: R$ <?= number_format($totalFinal, 2, ',', '.') ?></strong>
            </h5>
        <?php else: ?>
            <p>Nenhum item encontrado neste pedido.</p>
        <?php endif; ?>

        <a href="<?= site_url('pedido') ?>" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</div>