<!DOCTYPE html>
<html>
<head>
  <title>Pedidos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" />
</head>
<body>
<div class="container mt-4">
  <h1>Pedidos</h1>
  <a href="<?= site_url('pedido/criar') ?>" class="btn btn-primary mb-3">Novo Pedido</a>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Cliente</th>
        <th>Data</th>
        <th>Cupom</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pedidos as $pedido): ?>
        <tr>
          <td><?= htmlspecialchars($pedido['cliente']) ?></td>
          <td><?= htmlspecialchars($pedido['data_pedido']) ?></td>
          <td>
            <?php
              if (!empty($pedido['cupom_id'])) {
                $this->load->model('Cupom_model');
                $cupom = $this->Cupom_model->buscarPorId($pedido['cupom_id']);
                echo htmlspecialchars($cupom['codigo'] ?? 'N/A');
              } else {
                echo 'N/A';
              }
            ?>
          </td>
          <td>
            <a href="<?= site_url('pedido/editar/'.$pedido['id']) ?>" class="btn btn-sm btn-warning">Editar</a>
            <a href="<?= site_url('pedido/excluir/'.$pedido['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmar exclusão?')">Excluir</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($pedidos)): ?>
        <tr><td colspan="4" class="text-center">Nenhum pedido cadastrado</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
