<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        exigir_login(); // Protege rota

        $this->load->model('Produto_model');
        $this->load->model('Cupom_model');
        $this->load->model('Pedido_model');
        $this->load->model('Carrinho_model');
    }

    public function index() {
        $data['title'] = 'Dashboard';
        $data['carrinho_count'] = $this->Carrinho_model->contar_itens();

        $data['total_produtos'] = $this->Produto_model->contar();
        $data['total_cupons']   = $this->Cupom_model->contar();
        $data['total_pedidos']  = $this->Pedido_model->contar();
        $data['valor_total']    = $this->Pedido_model->somarTotal();

        $data['contents'] = $this->load->view('dashboard/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Testa o envio de email com debug detalhado
     */
    public function teste_email() {
        $this->load->helper('email');
        $this->load->library('email');
        
        $email_destino = $this->input->get('email') ?: 'lucasfelipeflima@gmail.com';
        
        echo "<h2>🧪 Teste de Envio de Email - DEBUG</h2>";
        echo "<p>Enviando email de teste para: <strong>$email_destino</strong></p>";
        echo "<hr>";
        
        // Mostrar configuração atual
        echo "<h3>📋 Configuração SMTP:</h3>";
        echo "<pre>";
        echo "Host: " . $this->email->smtp_host . "\n";
        echo "Porta: " . $this->email->smtp_port . "\n";
        echo "Usuário: " . $this->email->smtp_user . "\n";
        echo "Criptografia: " . $this->email->smtp_crypto . "\n";
        echo "</pre>";
        
        try {
            // Configurar email de teste
            $this->email->from('lucasfelipeflima@gmail.com', 'ERP Sistema');
            $this->email->to($email_destino);
            $this->email->subject('🧪 Teste de Email - ' . date('Y-m-d H:i:s'));
            
            $html = '
            <html>
            <body style="font-family: Arial, sans-serif; margin: 20px;">
                <div style="max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #007bff;">🎉 Email de Teste Funcionando!</h2>
                    <p>Este email foi enviado automaticamente pelo sistema ERP.</p>
                    <p><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</p>
                    <p><strong>Servidor:</strong> ' . $_SERVER['HTTP_HOST'] . '</p>
                    <div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">
                        <strong>✅ Sistema de email configurado corretamente!</strong>
                    </div>
                </div>
            </body>
            </html>';
            
            $this->email->message($html);
            
            echo "<h3>📤 Enviando email...</h3>";
            
            if ($this->email->send()) {
                echo "<div style='color: green; background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>✅ EMAIL ENVIADO COM SUCESSO!</strong><br>";
                echo "Verifique sua caixa de entrada (e spam) em: <strong>$email_destino</strong>";
                echo "</div>";
            } else {
                echo "<div style='color: red; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>❌ FALHA NO ENVIO</strong><br>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red; background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>❌ EXCEÇÃO:</strong><br>" . $e->getMessage();
            echo "</div>";
        }
        
        // Mostrar debug do email
        echo "<h3>🔍 Debug do Email:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
        echo htmlspecialchars($this->email->print_debugger());
        echo "</pre>";
        
        echo "<br><a href='" . base_url('dashboard') . "' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;'>← Voltar ao Dashboard</a>";
    }
}