<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-warehouse"></i> Gerenciar Estoque</h2>
            <p class="text-muted">Produto: <?= html_escape($produto->nome) ?></p>
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Informações do Produto
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Nome:</strong></td>
                            <td><?= html_escape($produto->nome) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Preço:</strong></td>
                            <td>R$ <?= number_format($produto->preco, 2, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Estoque Atual:</strong></td>
                            <td>
                                <span class="badge bg-<?= $produto->estoque > 0 ? 'success' : 'danger' ?> fs-6">
                                    <?= $produto->estoque ?> unidades
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?= $produto->ativo ? 'success' : 'secondary' ?>">
                                    <?= $produto->ativo ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                        </tr>
                        <?php if (!empty($produto->variacoes)): ?>
                        <tr>
                            <td><strong>Variações:</strong></td>
                            <td>
                                <?php $variacoes = json_decode($produto->variacoes, true); ?>
                                <?= count($variacoes) ?> variação(ões)
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> Atualizar Estoque
                    </h5>
                </div>
                <div class="card-body">
                    <?= form_open('produto/gerenciar_estoque/' . $produto->id) ?>
                        <div class="mb-3">
                            <label for="estoque" class="form-label">Nova Quantidade em Estoque</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="estoque" 
                                   name="estoque" 
                                   value="<?= $produto->estoque ?>" 
                                   min="0" 
                                   required>
                            <div class="form-text">
                                Informe a quantidade atual em estoque para este produto.
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Atualizar Estoque
                            </button>
                        </div>
                    <?= form_close() ?>
                </div>
            </div>

            <!-- Histórico rápido (opcional) -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Informações Adicionais
                    </h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">
                        <strong>Criado em:</strong> <?= date('d/m/Y H:i', strtotime($produto->criado_em)) ?><br>
                        <?php if (isset($produto->atualizado_em) && $produto->atualizado_em != $produto->criado_em): ?>
                        <strong>Última atualização:</strong> <?= date('d/m/Y H:i', strtotime($produto->atualizado_em)) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Foco no campo de estoque
    document.getElementById('estoque').focus();
    
    // Confirma mudanças significativas
    const form = document.querySelector('form');
    const estoqueInput = document.getElementById('estoque');
    const estoqueAtual = <?= $produto->estoque ?>;
    
    form.addEventListener('submit', function(e) {
        const novoEstoque = parseInt(estoqueInput.value);
        const diferenca = Math.abs(novoEstoque - estoqueAtual);
        
        if (diferenca > 10) {
            if (!confirm(`Tem certeza que deseja alterar o estoque de ${estoqueAtual} para ${novoEstoque} unidades?`)) {
                e.preventDefault();
            }
        }
    });
});
</script>
