<h2>Cupons</h2>

<a href="<?= site_url('cupom/create') ?>" class="btn btn-success mb-3">Novo Cupom</a>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Valor Mínimo</th>
            <th>Validade</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cupons as $cupom): ?>
        <?php 
            $valido = true;
            $status_texto = 'Ativo';
            $status_classe = 'text-success';
            
            if ($cupom['validade'] && $cupom['validade'] !== '0000-00-00') {
                if (strtotime($cupom['validade']) < time()) {
                    $valido = false;
                    $status_texto = 'Expirado';
                    $status_classe = 'text-danger';
                }
            }
        ?>
        <tr>
            <td><?= $cupom['id'] ?></td>
            <td><?= htmlspecialchars($cupom['codigo']) ?></td>
            <td><?= ucfirst($cupom['tipo']) ?></td>
            <td>
                <?= $cupom['tipo'] === 'percentual' ? $cupom['valor'].'%' : 'R$ '.number_format($cupom['valor'], 2, ',', '.') ?>
            </td>
            <td>
                <?= $cupom['minimo'] > 0 ? 'R$ '.number_format($cupom['minimo'], 2, ',', '.') : '<span class="text-muted">Sem mínimo</span>' ?>
            </td>
            <td>
                <?php if ($cupom['validade']): ?>
                    <?= date('d/m/Y', strtotime($cupom['validade'])) ?>
                <?php else: ?>
                    <span class="text-muted">Sem validade</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="<?= $status_classe ?>"><?= $status_texto ?></span>
            </td>
            <td>
                <a href="<?= site_url('cupom/edit/' . $cupom['id']) ?>" class="btn btn-primary btn-sm">Editar</a>
                <a href="<?= site_url('cupom/delete/' . $cupom['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Confirma exclusão?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>