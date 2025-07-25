<!DOCTYPE html>
<html>
<head>
  <title><?= isset($pedido) ? 'Editar' : 'Novo' ?> Pedido</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
</head>
<body>
<div class="container mt-4">
  <h1><?= isset($pedido) ? 'Editar' : 'Novo' ?> Pedido</h1>
  <form method="post" action="<?= site_url('pedido/salvar') ?>">
    <?php if (isset($pedido)): ?>
      <input type="hidden" name="id" value="<?= $pedido['id'] ?>" />
    <?php endif; ?>

    <div class="form-group">
      <label>Cliente</label>
      <input type="text" name="cliente" class="form-control" required value="<?= isset($pedido) ? htmlspecialchars($pedido['cliente']) : '' ?>" />
    </div>

    <div class="form-group">
      <label>Data do Pedido</label>
      <input type="date" name="data_pedido" class="form-control" required value="<?= isset($pedido) ? htmlspecialchars($pedido['data_pedido']) : date('Y-m-d') ?>" />
    </div>

    <div class="form-group">
      <label>Cupom</label>
      <select name="cupom_id" class="form-control">
        <option value="">Nenhum</option>
        <?php foreach ($cupons as $cupom): ?>
          <option value="<?= $cupom['id'] ?>" <?= (isset($pedido) && $pedido['cupom_id'] == $cupom['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cupom['codigo']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <h4>Itens do Pedido</h4>
    <table class="table table-bordered" id="itens-pedido">
      <thead>
        <tr>
          <th>Produto</th>
          <th>Quantidade</th>
          <th>Preço Unitário</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (isset($pedido['itens']) && count($pedido['itens']) > 0): ?>
          <?php foreach ($pedido['itens'] as $index => $item): ?>
            <tr>
              <td>
                <select name="itens[<?= $index ?>][produto_id]" class="form-control produto-select" required onchange="atualizarPreco(this, <?= $index ?>)">
                  <option value="">Selecione</option>
                  <?php foreach ($produtos as $produto): ?>
                    <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>"
                      <?= ($produto['id'] == $item['produto_id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($produto['nome']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="number" name="itens[<?= $index ?>][quantidade]" class="form-control quantidade-input" min="1" value="<?= $item['quantidade'] ?>" required /></td>
              <td><input type="number" name="itens[<?= $index ?>][preco_unitario]" class="form-control preco-input" step="0.01" value="<?= number_format($item['preco_unitario'], 2, '.', '') ?>" readonly /></td>
              <td><button type="button" class="btn btn-danger btn-sm btn-remover">Remover</button></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td>
              <select name="itens[0][produto_id]" class="form-control produto-select" required onchange="atualizarPreco(this, 0)">
                <option value="">Selecione</option>
                <?php foreach ($produtos as $produto): ?>
                  <option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="itens[0][quantidade]" class="form-control quantidade-input" min="1" value="1" required /></td>
            <td><input type="number" name="itens[0][preco_unitario]" class="form-control preco-input" step="0.01" readonly /></td>
            <td><button type="button" class="btn btn-danger btn-sm btn-remover">Remover</button></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <button type="button" id="btn-adicionar" class="btn btn-primary mb-3">Adicionar Item</button>

    <button type="submit" class="btn btn-success">Salvar Pedido</button>
    <a href="<?= site_url('pedido') ?>" class="btn btn-secondary">Voltar</a>
  </form>
</div>

<script>
  function atualizarPreco(selectElem, index) {
    const option = selectElem.options[selectElem.selectedIndex];
    const preco = option.getAttribute('data-preco') || 0;
    document.querySelector(`input[name="itens[${index}][preco_unitario]"]`).value = parseFloat(preco).toFixed(2);
  }

  document.querySelectorAll('.produto-select').forEach((selectElem, index) => {
    atualizarPreco(selectElem, index);
  });

  // Remover linha do item
  document.getElementById('itens-pedido').addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-remover')) {
      e.target.closest('tr').remove();
    }
  });

  // Adicionar nova linha
  document.getElementById('btn-adicionar').addEventListener('click', function() {
    const tbody = document.querySelector('#itens-pedido tbody');
    const index = tbody.querySelectorAll('tr').length;
    const novaLinha = document.createElement('tr');

    let options = '';
    <?php foreach ($produtos as $produto): ?>
      options += `<option value="<?= $produto['id'] ?>" data-preco="<?= $produto['preco'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>`;
    <?php endforeach; ?>

    novaLinha.innerHTML = `
      <td>
        <select name="itens[${index}][produto_id]" class="form-control produto-select" required onchange="atualizarPreco(this, ${index})">
          <option value="">Selecione</option>
          ${options}
        </select>
      </td>
      <td><input type="number" name="itens[${index}][quantidade]" class="form-control quantidade-input" min="1" value="1" required /></td>
      <td><input type="number" name="itens[${index}][preco_unitario]" class="form-control preco-input" step="0.01" readonly /></td>
      <td><button type="button" class="btn btn-danger btn-sm btn-remover">Remover</button></td>
    `;

    tbody.appendChild(novaLinha);
  });
</script>

</body>
</html>
