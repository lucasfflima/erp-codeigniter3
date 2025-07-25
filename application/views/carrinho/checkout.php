<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-credit-card"></i> Finalizar Compra</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('carrinho') ?>">Carrinho</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (validation_errors()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= validation_errors() ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('carrinho/processar_checkout') ?>" method="post" id="checkout-form">
        <div class="row">
            <!-- Dados de Entrega -->
            <div class="col-lg-8">
                <!-- Dados do Cliente -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Dados do Cliente</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="email_cliente" class="form-label">E-mail para confirmação *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email_cliente" 
                                       name="email_cliente" 
                                       value="<?= set_value('email_cliente') ?>"
                                       placeholder="seu-email@exemplo.com"
                                       required>
                                <div class="form-text">Você receberá a confirmação do pedido neste e-mail</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="cep" class="form-label">CEP *</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="cep" 
                                           name="cep" 
                                           value="<?= set_value('cep') ?>"
                                           placeholder="00000-000"
                                           maxlength="9"
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="btn-buscar-cep">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <div class="form-text">Digite o CEP para preenchimento automático</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="endereco" class="form-label">Endereço *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="endereco" 
                                       name="endereco" 
                                       value="<?= set_value('endereco') ?>"
                                       placeholder="Rua, Avenida, etc."
                                       required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="numero" class="form-label">Número *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="numero" 
                                       name="numero" 
                                       value="<?= set_value('numero') ?>"
                                       placeholder="123"
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="complemento" class="form-label">Complemento</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="complemento" 
                                       name="complemento" 
                                       value="<?= set_value('complemento') ?>"
                                       placeholder="Apartamento, Bloco, etc.">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="bairro" class="form-label">Bairro *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="bairro" 
                                       name="bairro" 
                                       value="<?= set_value('bairro') ?>"
                                       placeholder="Bairro"
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="cidade" class="form-label">Cidade *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="cidade" 
                                       name="cidade" 
                                       value="<?= set_value('cidade') ?>"
                                       placeholder="Cidade"
                                       required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="uf" class="form-label">UF *</label>
                                <select class="form-select" id="uf" name="uf" required>
                                    <option value="">Selecione</option>
                                    <option value="AC" <?= set_select('uf', 'AC') ?>>AC</option>
                                    <option value="AL" <?= set_select('uf', 'AL') ?>>AL</option>
                                    <option value="AP" <?= set_select('uf', 'AP') ?>>AP</option>
                                    <option value="AM" <?= set_select('uf', 'AM') ?>>AM</option>
                                    <option value="BA" <?= set_select('uf', 'BA') ?>>BA</option>
                                    <option value="CE" <?= set_select('uf', 'CE') ?>>CE</option>
                                    <option value="DF" <?= set_select('uf', 'DF') ?>>DF</option>
                                    <option value="ES" <?= set_select('uf', 'ES') ?>>ES</option>
                                    <option value="GO" <?= set_select('uf', 'GO') ?>>GO</option>
                                    <option value="MA" <?= set_select('uf', 'MA') ?>>MA</option>
                                    <option value="MT" <?= set_select('uf', 'MT') ?>>MT</option>
                                    <option value="MS" <?= set_select('uf', 'MS') ?>>MS</option>
                                    <option value="MG" <?= set_select('uf', 'MG') ?>>MG</option>
                                    <option value="PA" <?= set_select('uf', 'PA') ?>>PA</option>
                                    <option value="PB" <?= set_select('uf', 'PB') ?>>PB</option>
                                    <option value="PR" <?= set_select('uf', 'PR') ?>>PR</option>
                                    <option value="PE" <?= set_select('uf', 'PE') ?>>PE</option>
                                    <option value="PI" <?= set_select('uf', 'PI') ?>>PI</option>
                                    <option value="RJ" <?= set_select('uf', 'RJ') ?>>RJ</option>
                                    <option value="RN" <?= set_select('uf', 'RN') ?>>RN</option>
                                    <option value="RS" <?= set_select('uf', 'RS') ?>>RS</option>
                                    <option value="RO" <?= set_select('uf', 'RO') ?>>RO</option>
                                    <option value="RR" <?= set_select('uf', 'RR') ?>>RR</option>
                                    <option value="SC" <?= set_select('uf', 'SC') ?>>SC</option>
                                    <option value="SP" <?= set_select('uf', 'SP') ?>>SP</option>
                                    <option value="SE" <?= set_select('uf', 'SE') ?>>SE</option>
                                    <option value="TO" <?= set_select('uf', 'TO') ?>>TO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo dos Itens -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Itens do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($itens as $item): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <h6 class="mb-1"><?= html_escape($item['nome']) ?></h6>
                                    <?php if (!empty($item['variacao'])): ?>
                                        <small class="text-muted">
                                            <?php
                                            $variacao_data = json_decode($item['variacao'], true);
                                            if ($variacao_data) {
                                                foreach ($variacao_data as $attr => $valor) {
                                                    echo ucfirst($attr) . ': ' . html_escape($valor) . ' ';
                                                }
                                            }
                                            ?>
                                        </small>
                                    <?php endif; ?>
                                    <small class="text-muted d-block">Qtd: <?= $item['quantidade'] ?></small>
                                </div>
                                <div class="text-end">
                                    <strong>R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="col-lg-4">
                <div class="card sticky-top">
                    <div class="card-header">
                        <h5 class="mb-0">Resumo do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cupom de Desconto -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-ticket-alt"></i> Cupom de Desconto
                            </label>
                            <?php if (isset($totais['cupom']) && $totais['cupom']): ?>
                                <!-- Cupom Aplicado -->
                                <div class="alert alert-success py-2 mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small><i class="fas fa-check-circle"></i> <strong><?= $totais['cupom']['codigo'] ?></strong></small><br>
                                            <small class="text-muted">
                                                <?= $totais['cupom']['tipo'] === 'percentual' ? $totais['cupom']['valor'] . '% de desconto' : 'R$ ' . number_format($totais['cupom']['valor'], 2, ',', '.') . ' de desconto' ?>
                                            </small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remover-cupom">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Formulário para Aplicar Cupom -->
                                <div class="input-group input-group-sm" id="form-cupom">
                                    <input type="text" 
                                           class="form-control" 
                                           id="codigo-cupom" 
                                           placeholder="Digite o código do cupom"
                                           maxlength="20">
                                    <button class="btn btn-outline-primary" type="button" id="btn-aplicar-cupom">
                                        Aplicar
                                    </button>
                                </div>
                                <div id="cupom-feedback" class="form-text"></div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <!-- Totais -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="fw-bold" id="valor-subtotal">R$ <?= number_format($totais['subtotal'], 2, ',', '.') ?></span>
                        </div>
                        
                        <?php if (isset($totais['desconto']) && $totais['desconto'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success">Desconto:</span>
                            <span class="fw-bold text-success" id="valor-desconto">-R$ <?= number_format($totais['desconto'], 2, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Frete:</span>
                            <span class="fw-bold text-<?= $totais['frete'] == 0 ? 'success' : 'dark' ?>" id="valor-frete">
                                <?= $totais['frete'] == 0 ? 'Grátis' : 'R$ ' . number_format($totais['frete'], 2, ',', '.') ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total:</span>
                            <span class="h5 text-primary" id="valor-total">R$ <?= number_format($totais['total'], 2, ',', '.') ?></span>
                        </div>

                        <!-- Informações de Frete -->
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-truck"></i>
                            <strong>Entrega:</strong><br>
                            Prazo de entrega: 5 a 10 dias úteis
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100" id="btn-finalizar">
                            <i class="fas fa-check"></i> Finalizar Pedido
                        </button>
                        
                        <a href="<?= base_url('carrinho') ?>" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-arrow-left"></i> Voltar ao Carrinho
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Scripts -->
<script>
// Aguarda jQuery estar disponível
(function() {
    function initCheckout() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initCheckout, 100);
            return;
        }
        
        // Carrega jQuery Mask dinamicamente
        if (!jQuery.fn.mask) {
            jQuery.getScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js')
                .done(function() {
                    setupCheckout();
                })
                .fail(function() {
                    console.log('Erro ao carregar jQuery Mask');
                    setupCheckout(); // Continua sem a máscara
                });
        } else {
            setupCheckout();
        }
    }
    
    function setupCheckout() {
        jQuery(document).ready(function($) {
            // Máscara para CEP (só se a biblioteca estiver disponível)
            if ($.fn.mask) {
                $('#cep').mask('00000-000');
            } else {
                // Fallback: formatação manual do CEP
                $('#cep').on('input', function() {
                    let cep = $(this).val().replace(/\D/g, '');
                    if (cep.length > 5) {
                        cep = cep.substring(0, 5) + '-' + cep.substring(5, 8);
                    }
                    $(this).val(cep);
                });
            }

    // Buscar CEP
    $('#btn-buscar-cep, #cep').on('click blur', function() {
        let cep = $('#cep').val().replace(/\D/g, '');
        
        if (cep.length === 8) {
            buscarCEP(cep);
        }
    });

    // Enter no campo CEP
    $('#cep').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            let cep = $(this).val().replace(/\D/g, '');
            if (cep.length === 8) {
                buscarCEP(cep);
            }
        }
    });

    function buscarCEP(cep) {
        $.ajax({
            url: '<?= base_url('carrinho/buscar_cep') ?>',
            type: 'POST',
            data: { cep: cep },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-buscar-cep').html('<i class="fas fa-spinner fa-spin"></i>');
                $('#btn-buscar-cep').prop('disabled', true);
            },
            success: function(response) {
                if (response.sucesso) {
                    $('#endereco').val(response.logradouro);
                    $('#bairro').val(response.bairro);
                    $('#cidade').val(response.localidade);
                    $('#uf').val(response.uf);
                    
                    // Focar no campo número
                    $('#numero').focus();
                } else {
                    alert('CEP não encontrado');
                }
            },
            error: function() {
                alert('Erro ao buscar CEP');
            },
            complete: function() {
                $('#btn-buscar-cep').html('<i class="fas fa-search"></i>');
                $('#btn-buscar-cep').prop('disabled', false);
            }
        });
    }

    // Validação do formulário
    $('#checkout-form').on('submit', function(e) {
        let isValid = true;
        let firstInvalidField = null;

        // Validar campos obrigatórios
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
                
                if (!firstInvalidField) {
                    firstInvalidField = $(this);
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validar CEP
        let cep = $('#cep').val().replace(/\D/g, '');
        if (cep.length !== 8) {
            isValid = false;
            $('#cep').addClass('is-invalid');
            
            if (!firstInvalidField) {
                firstInvalidField = $('#cep');
            }
        }

        if (!isValid) {
            e.preventDefault();
            
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            
            alert('Por favor, preencha todos os campos obrigatórios');
        } else {
            // Desabilitar botão para evitar duplo clique
            $('#btn-finalizar').prop('disabled', true)
                              .html('<i class="fas fa-spinner fa-spin"></i> Processando...');
        }
    });

    // Remover classe de erro quando campo for preenchido
    $('[required]').on('input change', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
        }
    });

    // Aplicar cupom
    $('#btn-aplicar-cupom').on('click', function() {
        aplicarCupom();
    });

    // Enter no campo cupom
    $('#codigo-cupom').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            aplicarCupom();
        }
    });

    // Remover cupom
    $('#btn-remover-cupom').on('click', function() {
        removerCupom();
    });

    function aplicarCupom() {
        const codigo = $('#codigo-cupom').val().trim();
        
        if (!codigo) {
            mostrarFeedbackCupom('Digite um código de cupom', 'danger');
            return;
        }

        $.ajax({
            url: '<?= base_url('carrinho/aplicar_cupom') ?>',
            type: 'POST',
            data: { codigo_cupom: codigo },
            dataType: 'json',
            beforeSend: function() {
                $('#btn-aplicar-cupom').prop('disabled', true)
                                     .html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.sucesso) {
                    mostrarFeedbackCupom(response.mensagem, 'success');
                    atualizarTotais(response.totais);
                    
                    // Recarrega a página para mostrar cupom aplicado
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    mostrarFeedbackCupom(response.mensagem, 'danger');
                }
            },
            error: function() {
                mostrarFeedbackCupom('Erro ao aplicar cupom', 'danger');
            },
            complete: function() {
                $('#btn-aplicar-cupom').prop('disabled', false)
                                     .html('Aplicar');
            }
        });
    }

    function removerCupom() {
        $.ajax({
            url: '<?= base_url('carrinho/remover_cupom') ?>',
            type: 'POST',
            dataType: 'json',
            beforeSend: function() {
                $('#btn-remover-cupom').prop('disabled', true)
                                     .html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.sucesso) {
                    // Recarrega a página para remover cupom
                    location.reload();
                } else {
                    alert(response.mensagem);
                }
            },
            error: function() {
                alert('Erro ao remover cupom');
            },
            complete: function() {
                $('#btn-remover-cupom').prop('disabled', false)
                                     .html('<i class="fas fa-times"></i>');
            }
        });
    }

    function mostrarFeedbackCupom(mensagem, tipo) {
        const feedback = $('#cupom-feedback');
        feedback.removeClass('text-success text-danger')
                .addClass('text-' + tipo)
                .text(mensagem);
    }

    function atualizarTotais(totais) {
        $('#valor-subtotal').text('R$ ' + totais.subtotal.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        if (totais.desconto > 0) {
            $('#valor-desconto').text('-R$ ' + totais.desconto.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
        
        $('#valor-frete').text(totais.frete == 0 ? 'Grátis' : 'R$ ' + totais.frete.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
        
        $('#valor-total').text('R$ ' + totais.total.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }));
    }
        }); // fim do jQuery(document).ready
    }
    
    // Inicia quando página carregada
    initCheckout();
})();
</script>
