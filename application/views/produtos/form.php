<h2><?= isset($produto) ? 'Editar Produto' : 'Novo Produto' ?></h2>

<?php if (validation_errors()): ?>
    <div class="alert alert-danger">
        <?= validation_errors() ?>
    </div>
<?php endif; ?>

<form action="<?= isset($produto) ? site_url('produto/update/' . $produto->id) : site_url('produto/store') ?>" method="post" class="needs-validation" novalidate>
    <div class="mb-3">
        <label for="nome" class="form-label">Nome:</label>
        <input type="text" id="nome" name="nome" class="form-control" value="<?= set_value('nome', isset($produto) ? $produto->nome : '') ?>" required>
        <div class="invalid-feedback">Por favor, preencha o nome.</div>
    </div>

    <div class="mb-3">
        <label for="preco" class="form-label">Preço:</label>
        <input type="number" id="preco" name="preco" step="0.01" class="form-control" value="<?= set_value('preco', isset($produto) ? $produto->preco : '') ?>" required>
        <div class="invalid-feedback">Informe um preço válido.</div>
    </div>

    <div class="mb-3">
        <label for="estoque" class="form-label">Estoque:</label>
        <input type="number" id="estoque" name="estoque" class="form-control" value="<?= set_value('estoque', isset($produto) ? $produto->estoque : '') ?>" required>
        <div class="invalid-feedback">Informe o estoque disponível.</div>
    </div>

    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="<?= site_url('produto') ?>" class="btn btn-secondary ms-2">Voltar</a>
</form>

<script>
(() => {
  'use strict'
  const forms = document.querySelectorAll('.needs-validation')
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()
</script>
