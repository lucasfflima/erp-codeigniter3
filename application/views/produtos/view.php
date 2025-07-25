<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-box"></i> <?= html_escape($produto->nome) ?></h2>
            <p class="text-muted">
                <span class="badge bg-<?= $produto->ativo ? 'success' : 'secondary' ?>">
                    <?= $produto->ativo ? 'Ativo' : 'Inativo' ?>
                </span>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('produto') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
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

    <div class="row">
        <!-- Informações do Produto -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Placeholder para imagem do produto -->
                            <div class="text-center mb-3">
                                <?php if (!empty($produto->imagem)): ?>
                                    <img src="<?= base_url('uploads/produtos/' . $produto->imagem) ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?= html_escape($produto->nome) ?>"
                                         style="max-height: 300px;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="height: 300px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <h3 class="mb-3"><?= html_escape($produto->nome) ?></h3>
                            
                            <?php if (!empty($produto->descricao)): ?>
                                <div class="mb-3">
                                    <h6>Descrição:</h6>
                                    <p class="text-muted"><?= nl2br(html_escape($produto->descricao)) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <h4 class="text-primary">
                                    R$ <?= number_format($produto->preco, 2, ',', '.') ?>
                                </h4>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-warehouse"></i> 
                                    Estoque: <strong><?= $produto->estoque ?> unidades</strong>
                                </small>
                            </div>
                            
                            <?php if (!empty($produto->categoria)): ?>
                                <div class="mb-3">
                                    <span class="badge bg-info">
                                        <i class="fas fa-tag"></i> <?= html_escape($produto->categoria) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Variações (se houver) -->
            <?php if (!empty($produto->variacoes)): ?>
                <?php $variacoes = json_decode($produto->variacoes, true); ?>
                <?php if (!empty($variacoes)): ?>
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-palette"></i> Variações Disponíveis</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($variacoes as $tipo => $opcoes): ?>
                                    <div class="col-md-12 mb-3">
                                        <h6><?= ucfirst($tipo) ?>:</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php if (is_array($opcoes)): ?>
                                                <?php foreach ($opcoes as $opcao): ?>
                                                    <span class="badge bg-secondary">
                                                        <?= html_escape($opcao) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <?= html_escape($opcoes) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Formulário de Compra -->
        <div class="col-md-4">
            <div class="card sticky-top">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho</h6>
                </div>
                <div class="card-body">
                    <?php if ($produto->ativo && $produto->estoque > 0): ?>
                        <?= form_open('produto/adicionar_carrinho', ['id' => 'form-carrinho']) ?>
                            <input type="hidden" name="produto_id" value="<?= $produto->id ?>">
                            
                            <!-- Quantidade -->
                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade:</label>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-menos">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="form-control text-center" 
                                           id="quantidade" 
                                           name="quantidade" 
                                           value="1" 
                                           min="1" 
                                           max="<?= $produto->estoque ?>" 
                                           required>
                                    <button type="button" class="btn btn-outline-secondary" id="btn-mais">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Máximo: <?= $produto->estoque ?> unidades</small>
                            </div>
                            
                            <!-- Variação (se houver) -->
                            <?php if (!empty($produto->variacoes)): ?>
                                <?php $variacoes = json_decode($produto->variacoes, true); ?>
                                <?php if (!empty($variacoes)): ?>
                                    <?php foreach ($variacoes as $tipo => $opcoes): ?>
                                        <div class="mb-3">
                                            <label for="variacao_<?= $tipo ?>" class="form-label"><?= ucfirst($tipo) ?>:</label>
                                            <select class="form-select variacao-select" id="variacao_<?= $tipo ?>" name="variacao[<?= $tipo ?>]">
                                                <option value="">Selecione <?= $tipo ?></option>
                                                <?php if (is_array($opcoes)): ?>
                                                    <?php foreach ($opcoes as $opcao): ?>
                                                        <option value="<?= html_escape($opcao) ?>">
                                                            <?= html_escape($opcao) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="<?= html_escape($opcoes) ?>">
                                                        <?= html_escape($opcoes) ?>
                                                    </option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- Subtotal -->
                            <div class="mb-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <strong id="subtotal">R$ <?= number_format($produto->preco, 2, ',', '.') ?></strong>
                                </div>
                            </div>
                            
                            <!-- Botão Adicionar -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                                </button>
                                <a href="<?= base_url('carrinho') ?>" class="btn btn-outline-success">
                                    <i class="fas fa-credit-card"></i> Finalizar Compra
                                </a>
                            </div>
                        <?= form_close() ?>
                    <?php elseif (!$produto->ativo): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Produto não está disponível para venda.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            Produto fora de estoque.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Link para ver carrinho -->
                    <div class="mt-3 text-center">
                        <a href="<?= base_url('carrinho') ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye"></i> Ver Carrinho 
                            <span class="badge bg-primary"><?= $carrinho_count ?? 0 ?></span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Informações de Entrega -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-truck"></i> Entrega</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Entrega em até 5 dias úteis<br>
                        <i class="fas fa-shield-alt"></i> Compra 100% segura<br>
                        <i class="fas fa-undo"></i> 7 dias para trocas
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantidadeInput = document.getElementById('quantidade');
    const btnMenos = document.getElementById('btn-menos');
    const btnMais = document.getElementById('btn-mais');
    const subtotalElement = document.getElementById('subtotal');
    const precoUnitario = <?= $produto->preco ?>;
    const estoqueMax = <?= $produto->estoque ?>;
    
    // Função para atualizar subtotal
    function atualizarSubtotal() {
        const quantidade = parseInt(quantidadeInput.value) || 1;
        const subtotal = precoUnitario * quantidade;
        subtotalElement.textContent = 'R$ ' + subtotal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Botão menos
    btnMenos.addEventListener('click', function() {
        const atual = parseInt(quantidadeInput.value);
        if (atual > 1) {
            quantidadeInput.value = atual - 1;
            atualizarSubtotal();
        }
    });
    
    // Botão mais
    btnMais.addEventListener('click', function() {
        const atual = parseInt(quantidadeInput.value);
        if (atual < estoqueMax) {
            quantidadeInput.value = atual + 1;
            atualizarSubtotal();
        }
    });
    
    // Input quantidade
    quantidadeInput.addEventListener('input', atualizarSubtotal);
    
    // Validação do formulário
    document.getElementById('form-carrinho').addEventListener('submit', function(e) {
        const quantidade = parseInt(quantidadeInput.value);
        
        if (quantidade < 1 || quantidade > estoqueMax) {
            e.preventDefault();
            alert(`Quantidade deve estar entre 1 e ${estoqueMax} unidades.`);
            return false;
        }
        
        // Verificar se todas as variações foram selecionadas
        const variacaoSelects = this.querySelectorAll('.variacao-select');
        for (let select of variacaoSelects) {
            if (!select.value) {
                e.preventDefault();
                alert('Por favor, selecione todas as variações do produto.');
                return false;
            }
        }
        
        // Feedback visual
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adicionando...';
        submitBtn.disabled = true;
    });
});
</script>
