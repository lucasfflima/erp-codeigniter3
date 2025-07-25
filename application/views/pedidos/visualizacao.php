<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-file-invoice"></i> Pedidos Realizados</h2>
            <p class="text-muted">Visualização dos pedidos criados automaticamente através do checkout do site.</p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i> <?= $this->session->flashdata('info') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Nenhum pedido encontrado</h4>
            <p>Ainda não há pedidos realizados. Os pedidos são criados automaticamente quando clientes finalizam compras no site.</p>
            <hr>
            <p class="mb-0">
                <strong>Como funciona:</strong><br>
                1. Cliente adiciona produtos ao carrinho<br>
                2. Cliente preenche dados no checkout<br>
                3. Pedido é criado automaticamente<br>
                4. Cliente recebe e-mail de confirmação<br>
                5. Status pode ser atualizado via webhook
            </p>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Lista de Pedidos
                    <span class="badge bg-primary"><?= count($pedidos) ?> pedidos</span>
                </h5>
                <small class="text-muted">Atualizados automaticamente</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>E-mail Cliente</th>
                                <th>Itens</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Cupom</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <?php 
                                    // Definir cores do status
                                    $status_cores = [
                                        'pendente' => 'warning',
                                        'processando' => 'info',
                                        'enviado' => 'primary',
                                        'entregue' => 'success',
                                        'cancelado' => 'danger'
                                    ];
                                    $cor_status = $status_cores[$pedido['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><strong>#<?= $pedido['id'] ?></strong></td>
                                    <td>
                                        <?php if ($pedido['data_pedido']): ?>
                                            <?= date('d/m/Y H:i', strtotime($pedido['data_pedido'])) ?>
                                        <?php else: ?>
                                            <?= date('d/m/Y H:i', strtotime($pedido['criado_em'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($pedido['email_cliente']): ?>
                                            <i class="fas fa-envelope text-muted"></i> <?= $pedido['email_cliente'] ?>
                                        <?php else: ?>
                                            <span class="text-muted">Não informado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= count($pedido['itens']) ?> item(s)</span>
                                        <div class="small text-muted">
                                            <?php foreach ($pedido['itens'] as $index => $item): ?>
                                                <?= $item['nome'] ?> (<?= $item['quantidade'] ?>x)<?= $index < count($pedido['itens']) - 1 ? ', ' : '' ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $cor_status ?>">
                                            <?= ucfirst($pedido['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($pedido['cupom']): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-ticket-alt"></i> <?= $pedido['cupom']['codigo'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('pedido/show/' . $pedido['id']) ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informações sobre o sistema -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Como os Pedidos são Criados</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success"></i> Cliente adiciona produtos ao carrinho</li>
                            <li><i class="fas fa-check text-success"></i> Cliente preenche checkout com e-mail e endereço</li>
                            <li><i class="fas fa-check text-success"></i> Sistema cria pedido automaticamente</li>
                            <li><i class="fas fa-check text-success"></i> E-mail de confirmação é enviado</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-sync-alt"></i> Atualização de Status</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-webhook text-primary"></i> Status atualizado via webhook</li>
                            <li><i class="fas fa-envelope text-info"></i> Cliente recebe e-mail de atualização</li>
                            <li><i class="fas fa-trash text-danger"></i> Status "cancelado" remove o pedido</li>
                            <li><i class="fas fa-eye text-secondary"></i> Visualização apenas para consulta</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
