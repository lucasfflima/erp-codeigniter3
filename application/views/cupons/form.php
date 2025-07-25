<h2><?= isset($cupom) ? 'Editar Cupom' : 'Novo Cupom' ?></h2>

<?php if (validation_errors()): ?>
    <div class="alert alert-danger"><?= validation_errors() ?></div>
<?php endif; ?>

<form method="post" action="<?= isset($cupom) ? site_url('cupom/update/' . $cupom['id']) : site_url('cupom/store') ?>">
    <div class="mb-3">
        <label for="codigo" class="form-label">Código</label>
        <input type="text" id="codigo" name="codigo" class="form-control" required value="<?= set_value('codigo', $cupom['codigo'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label for="tipo" class="form-label">Tipo</label>
        <select id="tipo" name="tipo" class="form-select" required>
            <option value="">Selecione</option>
            <option value="percentual" <?= set_select('tipo', 'percentual', isset($cupom) && $cupom['tipo'] === 'percentual') ?>>Percentual</option>
            <option value="fixo" <?= set_select('tipo', 'fixo', isset($cupom) && $cupom['tipo'] === 'fixo') ?>>Valor Fixo</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="valor" class="form-label">Valor</label>
        <input type="number" step="0.01" id="valor" name="valor" class="form-control" required value="<?= set_value('valor', $cupom['valor'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label for="minimo" class="form-label">Valor Mínimo do Pedido</label>
        <input type="number" step="0.01" id="minimo" name="minimo" class="form-control" min="0" 
               value="<?= set_value('minimo', $cupom['minimo'] ?? '0.00') ?>">
        <div class="form-text">Valor mínimo do pedido para aplicar o cupom (50,00)</div>
    </div>

    <div class="mb-3">
        <label for="validade" class="form-label">Data de Validade</label>
        <input type="date" id="validade" name="validade" class="form-control" 
               value="<?= set_value('validade', (isset($cupom['validade']) && !empty($cupom['validade'])) ? $cupom['validade'] : '') ?>">
        <div class="form-text">Deixe em branco para cupom sem validade</div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="<?= site_url('cupom') ?>" class="btn btn-secondary ms-2">Voltar</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar data mínima como hoje
    const validadeInput = document.getElementById('validade');
    if (validadeInput) {
        const hoje = new Date().toISOString().split('T')[0];
        validadeInput.min = hoje;
        
        // Placeholder visual
        if (!validadeInput.value) {
            validadeInput.style.color = '#6c757d';
        }
        
        validadeInput.addEventListener('change', function() {
            this.style.color = this.value ? '#212529' : '#6c757d';
        });
    }
});
</script>