<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller para webhook de atualização de pedidos
 * Recebe notificações externas sobre mudanças de status
 */
class Webhook extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Desabilitar sessões para webhook (não são necessárias)
        $this->config->set_item('sess_driver', false);
        
        $this->load->model('Pedido_model');
        $this->load->helper('email');
        
        // Log de segurança para todas as requisições do webhook
        log_message('info', 'Webhook acessado - IP: ' . $this->input->ip_address() . ' - User Agent: ' . $this->input->user_agent());
    }

    /**
     * Endpoint principal do webhook
     * URL: /webhook/atualizar_pedido
     * Método: POST
     * 
     * Payload esperado:
     * {
     *   "pedido_id": 123,
     *   "status": "cancelado|processando|enviado|entregue",
     *   "token": "seu_token_secreto_aqui"
     * }
     */
    public function atualizar_pedido() {
        // Verificar método HTTP
        if ($this->input->method() !== 'post') {
            $this->resposta_webhook(405, 'Método não permitido', 'Apenas POST é aceito');
            return;
        }

        // Obter dados JSON do body
        $json_input = file_get_contents('php://input');
        $dados = json_decode($json_input, true);

        // Log da requisição
        log_message('info', 'Webhook payload recebido: ' . $json_input);

        // Validar JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->resposta_webhook(400, 'Erro de formato', 'JSON inválido: ' . json_last_error_msg());
            return;
        }

        // Validar token de segurança
        if (!$this->validar_token($dados)) {
            $this->resposta_webhook(401, 'Não autorizado', 'Token inválido ou ausente');
            return;
        }

        // Validar campos obrigatórios
        $validacao = $this->validar_dados_webhook($dados);
        if (!$validacao['valido']) {
            $this->resposta_webhook(400, 'Dados inválidos', $validacao['erro']);
            return;
        }

        // Processar atualização
        $resultado = $this->processar_atualizacao_pedido($dados['pedido_id'], $dados['status']);

        if ($resultado['sucesso']) {
            $this->resposta_webhook(200, 'Sucesso', $resultado['mensagem']);
        } else {
            $this->resposta_webhook(500, 'Erro interno', $resultado['mensagem']);
        }
    }

    /**
     * Valida token de segurança
     */
    private function validar_token($dados) {
        $token_esperado = $this->config->item('webhook_token') ?: 'webhook_token_secreto_2025';
        
        if (!isset($dados['token'])) {
            return false;
        }

        // Comparação segura contra timing attacks
        return hash_equals($token_esperado, $dados['token']);
    }

    /**
     * Valida dados do webhook
     */
    private function validar_dados_webhook($dados) {
        // Verificar campos obrigatórios
        if (empty($dados['pedido_id'])) {
            return ['valido' => false, 'erro' => 'Campo pedido_id é obrigatório'];
        }

        if (empty($dados['status'])) {
            return ['valido' => false, 'erro' => 'Campo status é obrigatório'];
        }

        // Validar ID do pedido
        if (!is_numeric($dados['pedido_id']) || $dados['pedido_id'] <= 0) {
            return ['valido' => false, 'erro' => 'pedido_id deve ser um número positivo'];
        }

        // Validar status permitidos
        $status_validos = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
        if (!in_array($dados['status'], $status_validos)) {
            return ['valido' => false, 'erro' => 'Status deve ser: ' . implode(', ', $status_validos)];
        }

        return ['valido' => true];
    }

    /**
     * Processa a atualização do pedido
     */
    private function processar_atualizacao_pedido($pedido_id, $novo_status) {
        try {
            // Verificar se o pedido existe
            $pedido = $this->Pedido_model->get_pedido_completo($pedido_id);
            
            if (!$pedido) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Pedido não encontrado: ' . $pedido_id
                ];
            }

            $status_anterior = $pedido['status'] ?? 'pendente';

            // Se status é "cancelado", remover o pedido
            if ($novo_status === 'cancelado') {
                $removido = $this->Pedido_model->remover_pedido($pedido_id);
                
                if ($removido) {
                    // Enviar e-mail de cancelamento
                    if (!empty($pedido['email_cliente'])) {
                        enviar_email_status_pedido($pedido_id, 'cancelado', $status_anterior);
                    }
                    
                    log_message('info', "Pedido {$pedido_id} removido via webhook");
                    
                    return [
                        'sucesso' => true,
                        'mensagem' => 'Pedido cancelado e removido com sucesso'
                    ];
                } else {
                    return [
                        'sucesso' => false,
                        'mensagem' => 'Erro ao remover pedido cancelado'
                    ];
                }
            } else {
                // Atualizar status do pedido
                $atualizado = $this->Pedido_model->atualizar_status($pedido_id, $novo_status);
                
                if ($atualizado) {
                    // Enviar e-mail de atualização
                    if (!empty($pedido['email_cliente'])) {
                        enviar_email_status_pedido($pedido_id, $novo_status, $status_anterior);
                    }
                    
                    log_message('info', "Pedido {$pedido_id} atualizado de '{$status_anterior}' para '{$novo_status}' via webhook");
                    
                    return [
                        'sucesso' => true,
                        'mensagem' => "Status atualizado para: {$novo_status}"
                    ];
                } else {
                    return [
                        'sucesso' => false,
                        'mensagem' => 'Erro ao atualizar status do pedido'
                    ];
                }
            }
            
        } catch (Exception $e) {
            log_message('error', 'Erro no webhook: ' . $e->getMessage());
            
            return [
                'sucesso' => false,
                'mensagem' => 'Erro interno do servidor'
            ];
        }
    }

    /**
     * Envia resposta padronizada do webhook
     */
    private function resposta_webhook($http_code, $status, $mensagem, $dados = []) {
        $this->output
            ->set_status_header($http_code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'timestamp' => date('c'),
                'status' => $status,
                'mensagem' => $mensagem,
                'dados' => $dados
            ], JSON_UNESCAPED_UNICODE));
        
        // Log da resposta
        log_message('info', "Webhook resposta {$http_code}: {$mensagem}");
    }

    /**
     * Endpoint para testar webhook (apenas desenvolvimento)
     */
    public function teste() {
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }

        $dados_teste = [
            'pedido_id' => 1,
            'status' => 'enviado',
            'token' => $this->config->item('webhook_token') ?: 'webhook_token_secreto_2025'
        ];

        echo "<h2>Teste do Webhook</h2>";
        echo "<p><strong>URL:</strong> " . base_url('webhook/atualizar_pedido') . "</p>";
        echo "<p><strong>Método:</strong> POST</p>";
        echo "<p><strong>Payload de teste:</strong></p>";
        echo "<pre>" . json_encode($dados_teste, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        
        echo "<p><strong>Teste via cURL:</strong></p>";
        echo "<pre>curl -X POST " . base_url('webhook/atualizar_pedido') . " \\
  -H 'Content-Type: application/json' \\
  -d '" . json_encode($dados_teste) . "'</pre>";
    }
}
