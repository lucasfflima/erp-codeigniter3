<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h2>
                <a href="<?= base_url('produto') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Continuar Comprando
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($itens)): ?>
        <!-- Carrinho Vazio -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                    <h3 class="text-muted">Seu carrinho está vazio</h3>
                    <p class="text-muted">Adicione alguns produtos para começar suas compras</p>
                    <a href="<?= base_url('produto') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Começar a Comprar
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Itens do Carrinho -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Itens no Carrinho (<?= count($itens) ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Preço</th>
                                        <th>Quantidade</th>
                                        <th>Subtotal</th>
                                        <th width="50">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item): ?>
                                        <tr id="item-<?= $item['produto_id'] ?>-<?= md5($item['variacao'] ?? '') ?>">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?= html_escape($item['nome']) ?></h6>
                                                        <?php if (!empty($item['variacao'])): ?>
                                                            <small class="text-muted">
                                                                <?php
                                                                $variacao_data = json_decode($item['variacao'], true);
                                                                if ($variacao_data) {
                                                                    foreach ($variacao_data as $attr => $valor) {
                                                                        echo ucfirst($attr) . ': ' . html_escape($valor) . ' ';
                                                                    }
                                                                }
                                                                ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong>R$ <?= number_format($item['preco'], 2, ',', '.') ?></strong>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm" style="width: 120px;">
                                                    <button class="btn btn-outline-secondary btn-decrease-qty" 
                                                            type="button" 
                                                            data-produto-id="<?= $item['produto_id'] ?>"
                                                            data-variacao='<?= html_escape($item['variacao']) ?>'>
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" 
                                                           class="form-control text-center qty-input" 
                                                           value="<?= $item['quantidade'] ?>" 
                                                           min="1" 
                                                           max="<?= $item['estoque_disponivel'] ?>"
                                                           data-produto-id="<?= $item['produto_id'] ?>"
                                                           data-variacao='<?= html_escape($item['variacao']) ?>'>
                                                    <button class="btn btn-outline-secondary btn-increase-qty" 
                                                            type="button"
                                                            data-produto-id="<?= $item['produto_id'] ?>"
                                                            data-variacao='<?= html_escape($item['variacao']) ?>'>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    Estoque: <?= $item['estoque_disponivel'] ?>
                                                </small>
                                            </td>
                                            <td>
                                                <strong class="item-subtotal">
                                                    R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger btn-remove-item" 
                                                        data-produto-id="<?= $item['produto_id'] ?>"
                                                        data-variacao='<?= html_escape($item['variacao']) ?>'
                                                        title="Remover item">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ações do Carrinho -->
                <div class="mt-3">
                    <button class="btn btn-outline-warning" id="btn-limpar-carrinho">
                        <i class="fas fa-trash-alt"></i> Limpar Carrinho
                    </button>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Subtotal:</span>
                            <span class="h5 text-primary" id="subtotal-value">R$ <?= number_format($totais['subtotal'], 2, ',', '.') ?></span>
                        </div>

                        <!-- Informação sobre frete -->
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>O frete será calculado na próxima etapa</strong><br>
                        </div>

                        <a href="<?= base_url('carrinho/checkout') ?>" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-credit-card"></i> Finalizar Compra
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script>
// Aguarda jQuery estar disponível
(function() {
    function initCarrinho() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initCarrinho, 100);
            return;
        }
        
        jQuery(document).ready(function($) {
            // Atualizar quantidade
            $('.qty-input').on('change input blur keyup', function() {
                updateQuantity($(this));
            });

    // Botões de aumentar/diminuir quantidade
    $('.btn-increase-qty').on('click', function() {
        let input = $(this).siblings('.qty-input');
        let currentValue = parseInt(input.val());
        let maxValue = parseInt(input.attr('max'));
        
        if (currentValue < maxValue) {
            input.val(currentValue + 1);
            updateQuantity(input);
        }
    });

    $('.btn-decrease-qty').on('click', function() {
        let input = $(this).siblings('.qty-input');
        let currentValue = parseInt(input.val());
        
        if (currentValue > 1) {
            input.val(currentValue - 1);
            updateQuantity(input);
        }
    });

    // Remover item
    $('.btn-remove-item').on('click', function() {
        if (confirm('Tem certeza que deseja remover este item?')) {
            removeItem($(this));
        }
    });

    // Limpar carrinho
    $('#btn-limpar-carrinho').on('click', function() {
        if (confirm('Tem certeza que deseja limpar todo o carrinho?')) {
            window.location.href = '<?= base_url('carrinho/limpar') ?>';
        }
    });

    function updateQuantity(input) {
        let produtoId = input.data('produto-id');
        let variacao = input.data('variacao');
        let quantidade = input.val();

        // Validação básica
        if (!produtoId || !quantidade || quantidade < 1) {
            return;
        }

        $.ajax({
            url: '<?= base_url('carrinho/atualizar_quantidade') ?>',
            type: 'POST',
            data: {
                produto_id: produtoId,
                variacao: variacao,
                quantidade: quantidade
            },
            dataType: 'json',
            beforeSend: function() {
                input.prop('disabled', true);
            },
            success: function(response) {
                if (response.sucesso) {
                    // Atualizar subtotal do item
                    let row = input.closest('tr');
                    row.find('.item-subtotal').text('R$ ' + response.subtotal.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    
                    // Atualizar subtotal geral
                    $('#subtotal-value').text('R$ ' + response.totais.subtotal.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    
                    // Atualizar contador do carrinho no header
                    updateCarrinhoCount();
                } else {
                    alert(response.mensagem);
                    input.val(response.quantidade_anterior || 1);
                }
            },
            error: function(xhr, status, error) {
                alert('Erro ao atualizar quantidade');
            },
            complete: function() {
                input.prop('disabled', false);
            }
        });
    }

    function removeItem(button) {
        let produtoId = button.data('produto-id');
        let variacao = button.data('variacao');

        $.ajax({
            url: '<?= base_url('carrinho/remover_item') ?>',
            type: 'POST',
            data: {
                produto_id: produtoId,
                variacao: variacao
            },
            dataType: 'json',
            beforeSend: function() {
                button.prop('disabled', true);
            },
            success: function(response) {
                if (response.sucesso) {
                    // Remover linha da tabela
                    let row = button.closest('tr');
                    row.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Se não há mais itens, recarregar a página
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        } else {
                            // Atualizar subtotal geral
                            $('#subtotal-value').text('R$ ' + response.totais.subtotal.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }));
                        }
                    });
                    
                    // Atualizar contador do carrinho
                    updateCarrinhoCount();
                } else {
                    alert(response.mensagem);
                }
            },
            error: function() {
                alert('Erro ao remover item');
            },
            complete: function() {
                button.prop('disabled', false);
            }
        });
    }

    function updateCarrinhoCount() {
        // Função para atualizar contador no header (se existir)
        $.get('<?= base_url('carrinho/contar_itens') ?>', function(count) {
            $('.carrinho-count').text(count);
            
            // Atualiza badge no navbar se existir
            $('.navbar .carrinho-count').text(count);
        });
    }

    // Intercepta eventos que modificam o carrinho para atualizar contador
    $(document).on('ajaxComplete', function(event, xhr, settings) {
        // Se foi uma requisição do carrinho, atualiza o contador
        if (settings.url && (
            settings.url.includes('carrinho/atualizar') ||
            settings.url.includes('carrinho/remover') ||
            settings.url.includes('produto/adicionar_carrinho')
        )) {
            setTimeout(updateCarrinhoCount, 200);
        }
    });
        }); // fim do jQuery(document).ready
    }
    
    // Inicia quando página carregada
    initCarrinho();
})();
</script>
