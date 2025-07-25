<div class="container mt-5">
    <h1 class="mb-4">Dashboard</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Produtos</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $total_produtos ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Cupons</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $total_cupons ?></h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Vendas Realizadas (R$)</div>
                <div class="card-body">
                    <h5 class="card-title">R$ <?= number_format($valor_total, 2, ',', '.') ?></h5>
                    <p class="card-text"><small><?= $total_pedidos ?> pedidos finalizados</small></p>
                </div>
            </div>
        </div>
    </div>
</div>