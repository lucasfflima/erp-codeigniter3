<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pedido extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth'); 
        exigir_login(); // Verifica se o usuário está logado
        $this->load->model('Pedido_model');
        $this->load->model('Produto_model');
        $this->load->model('Cupom_model');
        $this->load->helper('form');
        $this->load->helper('cupom');
    }

    public function index() {
        // Pedidos são criados automaticamente através do checkout
        // Exibir apenas visualização dos pedidos existentes
        $data['pedidos'] = $this->Pedido_model->get_all_com_relacoes();
        
        $this->load->view('layouts/main', [
            'title' => 'Pedidos - Visualização',
            'contents' => $this->load->view('pedidos/visualizacao', $data, true)
        ]);
    }

    // Desabilitar criação manual de pedidos
    public function create() {
        $this->session->set_flashdata('info', 'Pedidos são criados automaticamente quando clientes finalizam compras no site.');
        redirect('pedido');
    }

    // Desabilitar criação manual de pedidos
    public function store() {
        $this->session->set_flashdata('info', 'Pedidos são criados automaticamente quando clientes finalizam compras no site.');
        redirect('pedido');
    }

    public function show($id) {
        $data['pedido'] = $this->Pedido_model->get_com_relacoes($id);
        $this->load->view('pedidos/show', $data);
    }
}