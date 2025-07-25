<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-box"></i> Produtos</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('carrinho') ?>" class="btn btn-success me-2">
                <i class="fas fa-shopping-cart"></i> Ver Carrinho 
                <span class="badge bg-light text-dark"><?= $carrinho_count ?? 0 ?></span>
            </a>
            <a href="<?= base_url('produto/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Novo Produto
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

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="nome" class="form-label">Nome do Produto</label>
                    <input type="text" class="form-control" id="nome" name="nome" 
                           value="<?= $this->input->get('nome') ?>" placeholder="Digite o nome...">
                </div>
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoria</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas as categorias</option>
                        <!-- Aqui você pode adicionar categorias dinamicamente -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="ativo" <?= $this->input->get('status') == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                        <option value="inativo" <?= $this->input->get('status') == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <?php if (!empty($produtos)): ?>
        <div class="row">
            <?php foreach ($produtos as $produto): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= html_escape($produto->nome) ?></h5>
                            <p class="card-text text-muted"><?= html_escape($produto->descricao) ?></p>
                            
                            <div class="mb-2">
                                <span class="h5 text-primary">R$ <?= number_format($produto->preco, 2, ',', '.') ?></span>
                            </div>
                            
                            <!-- Status -->
                            <div class="mb-2">
                                <span class="badge bg-<?= $produto->ativo ? 'success' : 'secondary' ?>">
                                    <?= $produto->ativo ? 'Ativo' : 'Inativo' ?>
                                </span>
                                
                                <!-- Indicador de estoque -->
                                <?php if (($produto->estoque_total ?? 0) <= 5 && ($produto->estoque_total ?? 0) > 0): ?>
                                    <span class="badge bg-warning text-dark">Últimas unidades</span>
                                <?php elseif (($produto->estoque_total ?? 0) == 0): ?>
                                    <span class="badge bg-danger">Esgotado</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Estoque -->
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-warehouse"></i> 
                                    Estoque: <?= $produto->estoque_total ?? 0 ?>
                                </small>
                            </div>
                            
                            <!-- Variações (se houver) -->
                            <?php if (!empty($produto->variacoes)): ?>
                                <div class="mb-3">
                                    <small class="text-info">
                                        <i class="fas fa-palette"></i> 
                                        <?= count(json_decode($produto->variacoes, true)) ?> variações disponíveis
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <!-- Botão de compra rápida (se ativo e com estoque) -->
                            <?php if ($produto->ativo && ($produto->estoque_total ?? 0) > 0): ?>
                                <div class="row mb-2">
                                    <div class="col-12">
                                        <?= form_open('produto/adicionar_carrinho', ['class' => 'form-inline-carrinho']) ?>
                                            <input type="hidden" name="produto_id" value="<?= $produto->id ?>">
                                            <div class="input-group input-group-sm">
                                            </div>
                                        <?= form_close() ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <?= !$produto->ativo ? 'Produto inativo' : 'Fora de estoque' ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="btn-group w-100" role="group">
                                <a href="<?= base_url('produto/view/' . $produto->id) ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="<?= base_url('produto/edit/' . $produto->id) ?>" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?= base_url('produto/gerenciar_estoque/' . $produto->id) ?>" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-warehouse"></i> Estoque
                                </a>
                                <button class="btn btn-outline-danger btn-sm btn-delete" 
                                        data-id="<?= $produto->id ?>"
                                        data-nome="<?= html_escape($produto->nome) ?>">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginação (se necessário) -->
        <?php if (!empty($pagination)): ?>
            <div class="row">
                <div class="col-12">
                    <nav aria-label="Navegação de página">
                        <?= $pagination ?>
                    </nav>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-5x text-muted mb-3"></i>
            <h3 class="text-muted">Nenhum produto encontrado</h3>
            <p class="text-muted">Comece adicionando seu primeiro produto</p>
            <a href="<?= base_url('produto/form') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Adicionar Produto
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o produto <strong id="produto-nome"></strong>?</p>
                <p class="text-danger"><small>Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Excluir</a>
            </div>
        </div>
    </div>
</div>

<script>
// Aguarda jQuery estar disponível
(function() {
    function initProdutos() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initProdutos, 100);
            return;
        }
        
        jQuery(document).ready(function($) {
            // Modal de exclusão
            $('.btn-delete').on('click', function() {
                let produtoId = $(this).data('id');
                let produtoNome = $(this).data('nome');
                
                $('#produto-nome').text(produtoNome);
                $('#confirm-delete').attr('href', '<?= base_url('produto/delete/') ?>' + produtoId);
                $('#deleteModal').modal('show');
            });
        });
    }
    
    // Inicia quando página carregada
    initProdutos();
})();
</script>
