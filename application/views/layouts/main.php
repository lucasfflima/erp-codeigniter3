<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ERP CI3' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <style>
        .carrinho-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
        }
        .navbar-nav .nav-link {
            position: relative;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <i class="fas fa-store"></i> ERP CI3
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if ($this->session->userdata('usuario_logado')): ?>
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a href="<?= base_url('dashboard') ?>" class="nav-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('produto') ?>" class="nav-link">
                                <i class="fas fa-box"></i> Produtos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('cupom') ?>" class="nav-link">
                                <i class="fas fa-ticket-alt"></i> Cupons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('pedido') ?>" class="nav-link">
                                <i class="fas fa-eye"></i> Ver Pedidos
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <!-- Carrinho -->
                        <li class="nav-item">
                            <a href="<?= base_url('carrinho') ?>" class="nav-link">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="carrinho-badge carrinho-count">
                                    <?= $carrinho_count ?? 0 ?>
                                </span>
                            </a>
                        </li>
                        
                        <!-- Usuário -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= $this->session->userdata('usuario_logado')['nome'] ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a href="<?= base_url('auth/login') ?>" class="nav-link">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash Messages Globais -->
    <?php if ($this->session->flashdata('global_success')): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('global_success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('global_error')): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $this->session->flashdata('global_error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <main>
        <?= $contents ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <small class="text-muted">
                © <?= date('Y') ?> ERP CI3. Sistema de gestão empresarial.
            </small>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Verifica se jQuery está disponível
        if (typeof jQuery !== 'undefined') {
            // Função global para atualizar contador do carrinho
            function atualizarContadorCarrinho() {
                <?php if ($this->session->userdata('usuario_logado')): ?>
                    $.get('<?= base_url('api/carrinho/count') ?>', function(count) {
                    $('.carrinho-count').text(count);
                    
                    // Animação quando houver mudança
                    if (count > 0) {
                        $('.carrinho-count').addClass('d-inline-block');
                        $('.carrinho-count').addClass('animate__animated animate__pulse');
                        setTimeout(function() {
                            $('.carrinho-count').removeClass('animate__animated animate__pulse');
                        }, 1000);
                    } else {
                        $('.carrinho-count').removeClass('d-inline-block');
                    }
                }).fail(function() {
                    console.log('Erro ao atualizar contador do carrinho');
                });
            <?php endif; ?>
        }

        // Função global para interceptar adições ao carrinho
        $(document).on('submit', 'form[action*="adicionar_carrinho"]', function(e) {
            e.preventDefault();
            const form = $(this);
            const button = form.find('button[type="submit"]');
            const originalText = button.html();
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adicionando...');
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.sucesso) {
                        // Atualiza contador imediatamente
                        atualizarContadorCarrinho();
                        
                        // Mostra notificação de sucesso
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Adicionado!',
                                text: response.mensagem,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(response.mensagem);
                        }
                        
                        button.html('<i class="fas fa-check"></i> Adicionado!').removeClass('btn-success').addClass('btn-outline-success');
                        setTimeout(function() {
                            button.html(originalText).removeClass('btn-outline-success').addClass('btn-success').prop('disabled', false);
                        }, 2000);
                    } else {
                        alert(response.mensagem);
                        button.html(originalText).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Erro ao adicionar produto ao carrinho');
                    button.html(originalText).prop('disabled', false);
                }
            });
        });

        // Atualizar contador a cada 30 segundos (opcional)
        <?php if ($this->session->userdata('usuario_logado')): ?>
            setInterval(atualizarContadorCarrinho, 30000);
        <?php endif; ?>

        // Auto-hide alerts após 5 segundos
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        } // fim da verificação do jQuery
    </script>
</body>
</html>
