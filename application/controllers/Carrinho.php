<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carrinho extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        exigir_login();
        $this->load->model('Carrinho_model');
        $this->load->helper('cep');
        $this->load->helper('form');
        $this->load->helper('email');
        $this->load->library('form_validation');
    }

    /**
     * Visualiza carrinho de compras
     */
    public function index() {
        $data['itens'] = $this->Carrinho_model->get_itens();
        $data['totais'] = $this->Carrinho_model->get_total_com_cupom();
        $data['title'] = 'Carrinho de Compras';
        $data['carrinho_count'] = $this->Carrinho_model->contar_itens();
        
        $data['contents'] = $this->load->view('carrinho/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Atualiza quantidade de item no carrinho
     */
    public function atualizar_quantidade() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $produto_id = $this->input->post('produto_id');
        $quantidade = $this->input->post('quantidade', true);
        $variacao = $this->input->post('variacao');

        $resultado = $this->Carrinho_model->atualizar_quantidade_produto($produto_id, $quantidade, $variacao);

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode($resultado);
        } else {
            if ($resultado['sucesso']) {
                $this->session->set_flashdata('success', $resultado['mensagem']);
            } else {
                $this->session->set_flashdata('error', $resultado['mensagem']);
            }
            
            redirect('carrinho');
        }
    }

    /**
     * Remove item do carrinho
     */
    public function remover_item() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $produto_id = $this->input->post('produto_id');
        $variacao = $this->input->post('variacao');

        $resultado = $this->Carrinho_model->remover_item_produto($produto_id, $variacao);

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode($resultado);
        } else {
            if ($resultado['sucesso']) {
                $this->session->set_flashdata('success', $resultado['mensagem']);
            } else {
                $this->session->set_flashdata('error', $resultado['mensagem']);
            }
            
            redirect('carrinho');
        }
    }

    /**
     * Limpa carrinho
     */
    public function limpar() {
        $resultado = $this->Carrinho_model->limpar_carrinho();
        
        if ($resultado['sucesso']) {
            $this->session->set_flashdata('success', $resultado['mensagem']);
        } else {
            $this->session->set_flashdata('error', $resultado['mensagem']);
        }
        
        redirect('carrinho');
    }

    /**
     * Finaliza compra
     */
    public function checkout() {
        if ($this->Carrinho_model->esta_vazio()) {
            $this->session->set_flashdata('error', 'Carrinho está vazio');
            redirect('carrinho');
        }

        $data['itens'] = $this->Carrinho_model->get_itens();
        $data['totais'] = $this->Carrinho_model->get_total_com_cupom(); // Usa método com cupom
        $data['title'] = 'Finalizar Compra';
        $data['carrinho_count'] = $this->Carrinho_model->contar_itens();
        
        $data['contents'] = $this->load->view('carrinho/checkout', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Processa finalização da compra
     */
    public function processar_checkout() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        if ($this->Carrinho_model->esta_vazio()) {
            $this->session->set_flashdata('error', 'Carrinho está vazio');
            redirect('carrinho');
        }

        $this->form_validation->set_rules('cep', 'CEP', 'required|callback_validar_cep');
        $this->form_validation->set_rules('endereco', 'Endereço', 'required|min_length[10]');
        $this->form_validation->set_rules('email_cliente', 'E-mail', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            $this->checkout();
        } else {
            // Carrega helper de e-mail
            $this->load->helper('email');
            $this->load->model('Pedido_model');
            
            // Preparar dados do pedido
            $dados_pedido = [
                'produtos' => $this->Carrinho_model->get_itens(),
                'cupom_id' => $this->Carrinho_model->get_cupom_id(),
                'email_cliente' => $this->input->post('email_cliente'),
                'cep' => $this->input->post('cep'),
                'endereco_completo' => $this->input->post('endereco'),
                'cidade' => $this->input->post('cidade', true) ?: 'Não informado',
                'estado' => $this->input->post('estado', true) ?: 'Não informado',
                'total' => $this->Carrinho_model->get_total_com_cupom()['total']
            ];
            
            // Criar pedido no banco
            $pedido_criado = $this->Pedido_model->criar_pedido_com_itens($dados_pedido);
            
            if ($pedido_criado) {
                // Buscar ID do pedido criado para email
                $pedido_id = $this->db->insert_id();
                
                // Enviar e-mail de confirmação
                $email_enviado = enviar_email_pedido(['id' => $pedido_id]);
                
                if ($email_enviado) {
                    $this->session->set_flashdata('success', 'Pedido realizado com sucesso! Você receberá um e-mail de confirmação.');
                } else {
                    $this->session->set_flashdata('success', 'Pedido realizado com sucesso!');
                    log_message('error', 'Falha no envio de e-mail para pedido: ' . $pedido_id);
                }
                
                // Limpar carrinho
                $this->Carrinho_model->limpar_carrinho();
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Erro ao processar pedido. Tente novamente.');
                $this->checkout();
            }
        }
    }

    /**
     * Validação customizada de CEP
     */
    public function validar_cep($cep) {
        if (!validar_cep($cep)) {
            $this->form_validation->set_message('validar_cep', 'CEP inválido');
            return false;
        }

        // Tenta buscar o CEP para verificar se existe
        $dados_cep = buscar_cep($cep);
        if (!$dados_cep) {
            $this->form_validation->set_message('validar_cep', 'CEP não encontrado');
            return false;
        }

        return true;
    }

    /**
     * API para buscar CEP via AJAX
     */
    public function buscar_cep() {
        if ($this->input->is_ajax_request()) {
            $cep = $this->input->post('cep');
            header('Content-Type: application/json');
            echo buscar_cep_ajax($cep);
        } else {
            show_404();
        }
    }

    /**
     * Calcula frete para um CEP específico (para uso futuro)
     */
    public function calcular_frete() {
        if ($this->input->is_ajax_request()) {
            $cep = $this->input->post('cep');
            $subtotal = $this->Carrinho_model->get_subtotal();
            $frete = $this->Carrinho_model->calcular_frete($subtotal);
            
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'frete' => $frete,
                'subtotal' => $subtotal,
                'total' => $subtotal + $frete
            ]);
        } else {
            show_404();
        }
    }

    /**
     * API para contar itens do carrinho (para header)
     */
    public function contar_itens() {
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo $this->Carrinho_model->contar_itens();
        } else {
            echo $this->Carrinho_model->contar_itens();
        }
    }

    /**
     * API para obter totais do carrinho (para frete reativo)
     */
    public function obter_totais() {
        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            $totais = $this->Carrinho_model->get_total_com_cupom();
            $totais['count'] = $this->Carrinho_model->contar_itens();
            echo json_encode($totais);
        } else {
            show_404();
        }
    }

    /**
     * Aplica cupom de desconto
     */
    public function aplicar_cupom() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $codigo_cupom = $this->input->post('codigo_cupom');
        $resultado = $this->Carrinho_model->aplicar_cupom($codigo_cupom);

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            if ($resultado['sucesso']) {
                // Retorna novos totais com desconto
                $resultado['totais'] = $this->Carrinho_model->get_total_com_cupom();
            }
            echo json_encode($resultado);
        } else {
            if ($resultado['sucesso']) {
                $this->session->set_flashdata('success', $resultado['mensagem']);
            } else {
                $this->session->set_flashdata('error', $resultado['mensagem']);
            }
            redirect('carrinho/checkout');
        }
    }

    /**
     * Remove cupom aplicado
     */
    public function remover_cupom() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $resultado = $this->Carrinho_model->remover_cupom();

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            if ($resultado['sucesso']) {
                // Retorna novos totais sem desconto
                $resultado['totais'] = $this->Carrinho_model->get_total_com_cupom();
            }
            echo json_encode($resultado);
        } else {
            if ($resultado['sucesso']) {
                $this->session->set_flashdata('success', $resultado['mensagem']);
            } else {
                $this->session->set_flashdata('error', $resultado['mensagem']);
            }
            redirect('carrinho/checkout');
        }
    }
}
