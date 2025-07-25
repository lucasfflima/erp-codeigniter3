<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Controller
 * Controlador base com funcionalidades comuns
 */
class Base_Controller extends CI_Controller {

    protected $data = [];

    public function __construct() {
        parent::__construct();
        
        // Carrega helpers e libraries básicos
        $this->load->helper(['url', 'form', 'html']);
        $this->load->library('session');
        
        // Se usuário logado, carrega contador do carrinho
        if ($this->session->userdata('usuario_logado')) {
            $this->load->model('Carrinho_model');
            $this->data['carrinho_count'] = $this->Carrinho_model->contar_itens();
        } else {
            $this->data['carrinho_count'] = 0;
        }
    }

    /**
     * Carrega uma view com o layout padrão
     */
    protected function load_view($view, $data = []) {
        // Mescla dados do controller com dados globais
        $data = array_merge($this->data, $data);
        
        // Carrega a view principal
        $data['contents'] = $this->load->view($view, $data, true);
        
        // Carrega o layout
        $this->load->view('layouts/main', $data);
    }

    /**
     * Retorna dados JSON para AJAX
     */
    protected function output_json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Verifica se é requisição AJAX
     */
    protected function is_ajax() {
        return $this->input->is_ajax_request();
    }

    /**
     * Redireciona com mensagem de sucesso
     */
    protected function redirect_with_success($url, $message) {
        $this->session->set_flashdata('success', $message);
        redirect($url);
    }

    /**
     * Redireciona com mensagem de erro
     */
    protected function redirect_with_error($url, $message) {
        $this->session->set_flashdata('error', $message);
        redirect($url);
    }
}
