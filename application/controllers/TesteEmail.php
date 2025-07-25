<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TesteEmail extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        exigir_login();
        $this->load->helper('email');
    }

    /**
     * Testa o envio de email
     */
    public function index() {
        // Pega o email do usuário logado ou usa um padrão
        $usuario = $this->session->userdata('usuario_logado');
        $email_destino = $this->input->get('email') ?: $usuario['email'] ?? 'teste@exemplo.com';
        
        echo "<h2>Teste de Envio de Email</h2>";
        echo "<p>Testando envio para: <strong>$email_destino</strong></p>";
        echo "<hr>";
        
        $resultado = testar_envio_email($email_destino);
        
        if ($resultado['sucesso']) {
            echo "<div style='color: green; background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px;'>";
            echo "✅ " . $resultado['mensagem'];
            echo "</div>";
        } else {
            echo "<div style='color: red; background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "❌ " . $resultado['mensagem'];
            echo "</div>";
        }
        
        echo "<br><br>";
        echo "<a href='" . base_url('dashboard') . "' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>← Voltar ao Dashboard</a>";
        
        // Mostrar logs recentes
        echo "<hr><h3>Logs Recentes:</h3>";
        echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; white-space: pre-line;'>";
        
        $log_file = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
        if (file_exists($log_file)) {
            $logs = file_get_contents($log_file);
            $lines = explode("\n", $logs);
            $recent_lines = array_slice($lines, -20); // Últimas 20 linhas
            echo implode("\n", $recent_lines);
        } else {
            echo "Arquivo de log não encontrado: $log_file";
        }
        
        echo "</div>";
    }
    
    /**
     * Testa especificamente o envio de email de pedido
     */
    public function pedido() {
        // Busca o último pedido para teste
        $this->load->model('Pedido_model');
        $pedidos = $this->db->order_by('id', 'DESC')->limit(1)->get('pedidos')->result_array();
        
        if (empty($pedidos)) {
            echo "<h2>Erro</h2>";
            echo "<p>Nenhum pedido encontrado para teste. Faça um pedido primeiro.</p>";
            echo "<a href='" . base_url('dashboard') . "'>← Voltar</a>";
            return;
        }
        
        $pedido = $pedidos[0];
        
        echo "<h2>Teste de Email de Pedido</h2>";
        echo "<p>Testando envio para pedido #" . $pedido['id'] . "</p>";
        echo "<p>Email: <strong>" . $pedido['email_cliente'] . "</strong></p>";
        echo "<hr>";
        
        $resultado = enviar_email_pedido(['id' => $pedido['id']]);
        
        if ($resultado) {
            echo "<div style='color: green; background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px;'>";
            echo "✅ Email de pedido enviado com sucesso!";
            echo "</div>";
        } else {
            echo "<div style='color: red; background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px;'>";
            echo "❌ Erro ao enviar email de pedido. Verifique os logs.";
            echo "</div>";
        }
        
        echo "<br><br>";
        echo "<a href='" . base_url('dashboard') . "' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>← Voltar ao Dashboard</a>";
    }
}
