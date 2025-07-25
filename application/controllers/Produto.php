<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produto extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        exigir_login();
        $this->load->model('Produto_model');
        $this->load->model('Carrinho_model');
        $this->load->helper('cep');
        $this->load->helper('form');
        $this->load->library('form_validation');
    }

    /**
     * Lista todos os produtos
     */
    public function index() {
        $filtros = [
            'nome' => $this->input->get('nome'),
            'categoria' => $this->input->get('categoria'),
            'status' => $this->input->get('status')
        ];

        // Busca produtos com informações de estoque da tabela separada
        if (empty($filtros['nome']) && empty($filtros['categoria']) && empty($filtros['status'])) {
            $data['produtos'] = $this->Produto_model->get_all_with_estoque();
        } else {
            $data['produtos'] = $this->Produto_model->listar($filtros);
            // Adiciona informação de estoque para produtos filtrados
            foreach ($data['produtos'] as &$produto) {
                $produto->estoque_total = $this->Produto_model->Estoque_model->total_estoque_produto($produto->id);
            }
        }
        
        $data['title'] = 'Produtos';
        $data['carrinho_count'] = $this->Carrinho_model->contar_itens();
        
        $data['contents'] = $this->load->view('produtos/index', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function create() {
        $data['title'] = 'Novo Produto';
        $data['produto'] = null;
        $data['contents'] = $this->load->view('produtos/form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function edit($id) {
        $data['produto'] = $this->Produto_model->find($id);
        
        if (!$data['produto']) {
            show_404();
        }
        
        // Busca estoque total da tabela estoque separada
        $data['produto']->estoque = $this->Produto_model->Estoque_model->total_estoque_produto($id);
        
        $data['title'] = 'Editar Produto';
        $data['contents'] = $this->load->view('produtos/form', $data, true);
        $this->load->view('layouts/main', $data);
    }

    public function store() {
        $this->form_validation->set_rules('nome', 'Nome', 'required|min_length[3]');
        $this->form_validation->set_rules('preco', 'Preço', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('estoque_inicial', 'Estoque Inicial', 'integer|greater_than_equal_to[0]');

        if ($this->form_validation->run() === FALSE) {
            $data['title'] = 'Novo Produto';
            $data['produto'] = null;
            $data['contents'] = $this->load->view('produtos/form', $data, true);
            $this->load->view('layouts/main', $data);
        } else {
            $produto_data = [
                'nome' => $this->input->post('nome'),
                'preco' => $this->input->post('preco'),
                'estoque_inicial' => $this->input->post('estoque_inicial', true) ?: 0
            ];

            // Processa variações se informadas
            $variacoes_raw = $this->input->post('variacoes');
            if (!empty($variacoes_raw)) {
                $variacoes = [];
                $linhas = explode("\n", $variacoes_raw);
                
                foreach ($linhas as $linha) {
                    $linha = trim($linha);
                    if (!empty($linha)) {
                        $variacoes[] = $linha;
                    }
                }
                
                if (!empty($variacoes)) {
                    $produto_data['variacoes'] = $variacoes;
                }
            }

            if ($this->Produto_model->insert($produto_data)) {
                $this->session->set_flashdata('success', 'Produto cadastrado com sucesso!');
            } else {
                $this->session->set_flashdata('error', 'Erro ao cadastrar produto.');
            }
            
            redirect('produto');
        }
    }

    public function update($id) {
        $produto = $this->Produto_model->find($id);
        
        if (!$produto) {
            show_404();
        }

        $this->form_validation->set_rules('nome', 'Nome', 'required|min_length[3]');
        $this->form_validation->set_rules('preco', 'Preço', 'required|numeric|greater_than[0]');
        $this->form_validation->set_rules('estoque', 'Estoque', 'integer|greater_than_equal_to[0]');

        if ($this->form_validation->run() === FALSE) {
            $data['produto'] = $produto;
            // Adiciona estoque atual para o formulário
            $data['produto']->estoque = $this->Produto_model->Estoque_model->total_estoque_produto($id);
            $data['title'] = 'Editar Produto';
            $data['contents'] = $this->load->view('produtos/form', $data, true);
            $this->load->view('layouts/main', $data);
        } else {
            $produto_data = [
                'nome' => $this->input->post('nome'),
                'preco' => $this->input->post('preco')
            ];

            // Processa variações se informadas
            $variacoes_raw = $this->input->post('variacoes');
            if (!empty($variacoes_raw)) {
                $variacoes = [];
                $linhas = explode("\n", $variacoes_raw);
                
                foreach ($linhas as $linha) {
                    $linha = trim($linha);
                    if (!empty($linha)) {
                        $variacoes[] = $linha;
                    }
                }
                
                $produto_data['variacoes'] = empty($variacoes) ? null : $variacoes;
            } else {
                $produto_data['variacoes'] = null;
            }

            if ($this->Produto_model->update($id, $produto_data)) {
                // Atualiza estoque se informado
                $estoque = $this->input->post('estoque');
                if (is_numeric($estoque) && $estoque >= 0) {
                    $this->Produto_model->Estoque_model->atualizar_estoque($id, null, (int)$estoque);
                }
                
                $this->session->set_flashdata('success', 'Produto atualizado com sucesso!');
            } else {
                $this->session->set_flashdata('error', 'Erro ao atualizar produto.');
            }
            
            redirect('produto');
        }
    }

    public function delete($id) {
        if ($this->Produto_model->delete($id)) {
            $this->session->set_flashdata('success', 'Produto removido com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao remover produto.');
        }
        
        redirect('produto');
    }

    /**
     * Página de visualização completa do produto com opção de compra
     */
    public function view($id) {
        $data['produto'] = $this->Produto_model->find($id);
        
        if (!$data['produto']) {
            show_404();
        }
        
        // Busca estoque da tabela separada
        $data['produto']->estoque = $this->Produto_model->Estoque_model->total_estoque_produto($id);
        
        $data['title'] = $data['produto']->nome;
        $data['carrinho_count'] = $this->Carrinho_model->contar_itens();
        $data['contents'] = $this->load->view('produtos/view', $data, true);
        $this->load->view('layouts/main', $data);
    }

    /**
     * Adiciona produto ao carrinho
     */
    public function adicionar_carrinho() {
        if ($this->input->method() !== 'post') {
            show_404();
        }

        $produto_id = $this->input->post('produto_id');
        $quantidade = $this->input->post('quantidade', true) ?: 1;
        $variacao = $this->input->post('variacao');

        $resultado = $this->Carrinho_model->adicionar_item($produto_id, $quantidade, $variacao);

        if ($this->input->is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode($resultado);
        } else {
            if ($resultado['sucesso']) {
                $this->session->set_flashdata('success', $resultado['mensagem']);
            } else {
                $this->session->set_flashdata('error', $resultado['mensagem']);
            }
            
            redirect('produto/view/' . $produto_id);
        }
    }

    /**
     * Gerencia estoque do produto (versão simplificada)
     */
    public function gerenciar_estoque($produto_id) {
        $data['produto'] = $this->Produto_model->find($produto_id);
        
        if (!$data['produto']) {
            show_404();
        }
        
        // Busca estoque atual da tabela estoque separada
        $data['produto']->estoque = $this->Produto_model->Estoque_model->total_estoque_produto($produto_id);
        
        if ($this->input->method() === 'post') {
            $novo_estoque = $this->input->post('estoque');
            
            if (is_numeric($novo_estoque) && $novo_estoque >= 0) {
                // Atualiza estoque na tabela separada
                if ($this->Produto_model->Estoque_model->atualizar_estoque($produto_id, null, (int)$novo_estoque)) {
                    $this->session->set_flashdata('success', 'Estoque atualizado com sucesso!');
                } else {
                    $this->session->set_flashdata('error', 'Erro ao atualizar estoque');
                }
                
                redirect('produto/gerenciar_estoque/' . $produto_id);
            }
        }
        
        $data['title'] = 'Gerenciar Estoque - ' . $data['produto']->nome;
        $data['carrinho_count'] = $this->Carrinho_model->contar_itens();
        $data['contents'] = $this->load->view('produtos/estoque', $data, true);
        $this->load->view('layouts/main', $data);
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
}
