<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper para envio de e-mails do sistema
 */

if (!function_exists('enviar_email_pedido')) {
    /**
     * Envia e-mail de confirmação de pedido
     */
    function enviar_email_pedido($pedido_data) {
        $CI = &get_instance();
        $CI->load->library('email');
        $CI->load->model(['Pedido_model', 'Produto_model']);
        
        try {
            // Buscar dados completos do pedido
            $pedido = $CI->Pedido_model->get_pedido_completo($pedido_data['id']);
            
            if (!$pedido) {
                log_message('error', 'Pedido não encontrado para envio de e-mail: ' . $pedido_data['id']);
                return false;
            }
            
            // Configurar e-mail
            $CI->email->from($CI->config->item('from_email') ?: 'noreply@sistema.com', 
                            $CI->config->item('from_name') ?: 'ERP Sistema');
            $CI->email->to($pedido['email_cliente']);
            $CI->email->subject('Confirmação do Pedido #' . $pedido['id']);
            
            // Gerar HTML do e-mail
            $html = gerar_html_email_pedido($pedido);
            $CI->email->message($html);
            
            // Enviar e-mail
            if ($CI->email->send()) {
                log_message('info', 'E-mail enviado com sucesso para: ' . $pedido['email_cliente']);
                return true;
            } else {
                log_message('error', 'Erro ao enviar e-mail: ' . $CI->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exceção no envio de e-mail: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('gerar_html_email_pedido')) {
    /**
     * Gera HTML para e-mail de confirmação de pedido
     */
    function gerar_html_email_pedido($pedido) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Confirmação de Pedido</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .pedido-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .endereco { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .itens { margin: 20px 0; }
                .item { border-bottom: 1px solid #dee2e6; padding: 10px 0; }
                .total { font-size: 18px; font-weight: bold; color: #28a745; text-align: right; margin-top: 20px; }
                .footer { background: #6c757d; color: white; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🎉 Pedido Confirmado!</h1>
                    <p>Obrigado pela sua compra!</p>
                </div>
                
                <div class="content">
                    <div class="pedido-info">
                        <h3>📋 Informações do Pedido</h3>
                        <p><strong>Número:</strong> #' . $pedido['id'] . '</p>
                        <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($pedido['data_pedido'])) . '</p>
                        <p><strong>Status:</strong> ' . ucfirst($pedido['status']) . '</p>
                    </div>
                    
                    <div class="endereco">
                        <h3>📍 Endereço de Entrega</h3>
                        <p>' . $pedido['endereco_completo'] . '</p>
                        <p>' . $pedido['cidade'] . ' - ' . $pedido['estado'] . '</p>
                        <p>CEP: ' . $pedido['cep'] . '</p>
                    </div>
                    
                    <div class="itens">
                        <h3>🛍️ Itens do Pedido</h3>';
        
        // Adicionar itens do pedido
        if (isset($pedido['itens'])) {
            foreach ($pedido['itens'] as $item) {
                $html .= '
                        <div class="item">
                            <strong>' . $item['nome'] . '</strong><br>
                            Quantidade: ' . $item['quantidade'] . ' x R$ ' . number_format($item['preco'], 2, ',', '.') . ' = 
                            <strong>R$ ' . number_format($item['quantidade'] * $item['preco'], 2, ',', '.') . '</strong>
                        </div>';
            }
        }
        
        $html .= '
                    </div>
                    
                    <div class="total">
                        💰 Total: R$ ' . number_format($pedido['total'], 2, ',', '.') . '
                    </div>
                    
                    <p style="margin-top: 30px;">
                        <strong>Próximos passos:</strong><br>
                        • Seu pedido está sendo processado<br>
                        • Você receberá atualizações sobre o status<br>
                        • Em caso de dúvidas, entre em contato conosco
                    </p>
                </div>
                
                <div class="footer">
                    <p>ERP Sistema - Seu e-commerce de confiança</p>
                    <p>Este é um e-mail automático, não responda.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}

if (!function_exists('enviar_email_status_pedido')) {
    /**
     * Envia e-mail de atualização de status do pedido
     */
    function enviar_email_status_pedido($pedido_id, $novo_status, $status_anterior = null) {
        $CI = &get_instance();
        $CI->load->library('email');
        $CI->load->model('Pedido_model');
        
        try {
            $pedido = $CI->Pedido_model->get_pedido_completo($pedido_id);
            
            if (!$pedido) {
                return false;
            }
            
            // Configurar e-mail
            $CI->email->from($CI->config->item('from_email') ?: 'noreply@sistema.com', 
                            $CI->config->item('from_name') ?: 'ERP Sistema');
            $CI->email->to($pedido['email_cliente']);
            $CI->email->subject('Atualização do Pedido #' . $pedido['id'] . ' - ' . ucfirst($novo_status));
            
            // HTML para atualização de status
            $html = gerar_html_status_pedido($pedido, $novo_status, $status_anterior);
            $CI->email->message($html);
            
            return $CI->email->send();
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao enviar e-mail de status: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('gerar_html_status_pedido')) {
    /**
     * Gera HTML para e-mail de atualização de status
     */
    function gerar_html_status_pedido($pedido, $novo_status, $status_anterior) {
        $status_icons = [
            'pendente' => '⏳',
            'processando' => '🔄',
            'enviado' => '📦',
            'entregue' => '✅',
            'cancelado' => '❌'
        ];
        
        $status_colors = [
            'pendente' => '#ffc107',
            'processando' => '#007bff',
            'enviado' => '#17a2b8',
            'entregue' => '#28a745',
            'cancelado' => '#dc3545'
        ];
        
        $icon = $status_icons[$novo_status] ?? '📋';
        $color = $status_colors[$novo_status] ?? '#6c757d';
        
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Atualização de Pedido</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: ' . $color . '; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .status-box { background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center; margin: 20px 0; border-left: 4px solid ' . $color . '; }
                .pedido-info { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . $icon . ' Status Atualizado</h1>
                    <p>Seu pedido foi atualizado</p>
                </div>
                
                <div class="content">
                    <div class="status-box">
                        <h2>Status Atual: ' . ucfirst($novo_status) . '</h2>
                        ' . ($status_anterior ? '<p>Status anterior: ' . ucfirst($status_anterior) . '</p>' : '') . '
                    </div>
                    
                    <div class="pedido-info">
                        <h3>📋 Detalhes do Pedido</h3>
                        <p><strong>Número:</strong> #' . $pedido['id'] . '</p>
                        <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($pedido['data_pedido'])) . '</p>
                        <p><strong>Total:</strong> R$ ' . number_format($pedido['total'], 2, ',', '.') . '</p>
                    </div>
                </div>
            </div>
        </body>
        </html>';
    }
}

if (!function_exists('testar_envio_email')) {
    /**
     * Função de teste para verificar se o envio de email está funcionando
     */
    function testar_envio_email($email_destino) {
        $CI = &get_instance();
        $CI->load->library('email');
        
        try {
            // Configurar e-mail de teste
            $CI->email->from($CI->config->item('from_email') ?: 'noreply@localhost', 
                            $CI->config->item('from_name') ?: 'ERP Sistema');
            $CI->email->to($email_destino);
            $CI->email->subject('Teste de Email - ERP Sistema');
            
            $html = '
            <html>
            <body style="font-family: Arial, sans-serif; margin: 20px;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #007bff;">🎉 Teste de Email</h2>
                    <p>Este é um email de teste do sistema ERP.</p>
                    <p><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</p>
                    <p><strong>Servidor:</strong> ' . $_SERVER['HTTP_HOST'] . '</p>
                    <p style="color: #28a745;"><strong>✅ Sistema de email funcionando corretamente!</strong></p>
                    <hr>
                    <small>Este email foi enviado automaticamente pelo sistema ERP CI3.</small>
                </div>
            </body>
            </html>';
            
            $CI->email->message($html);
            
            // Enviar e-mail
            if ($CI->email->send()) {
                log_message('info', 'Email de teste enviado com sucesso para: ' . $email_destino);
                return [
                    'sucesso' => true,
                    'mensagem' => 'Email de teste enviado com sucesso!'
                ];
            } else {
                $debug = $CI->email->print_debugger();
                log_message('error', 'Erro ao enviar email de teste: ' . $debug);
                return [
                    'sucesso' => false,
                    'mensagem' => 'Erro ao enviar email: ' . $debug
                ];
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exceção no teste de email: ' . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
}
