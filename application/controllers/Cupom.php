<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cupom extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('auth');
        exigir_login();
        $this->load->model('Cupom_model');
        
        // Força carregamento dos helpers necessários para garantir funcionamento
        $this->load->helper('form');
        $this->load->library('form_validation');
    }

    public function index() {
        $data['cupons'] = $this->Cupom_model->get_all();
        $this->load->view('layouts/main', [
            'title' => 'Cupons',
            'contents' => $this->load->view('cupons/index', $data, true)
        ]);
    }

    public function create() {
        $this->load->view('layouts/main', [
            'title' => 'Novo Cupom',
            'contents' => $this->load->view('cupons/form', [], true)
        ]);
    }

    public function store() {
        $this->form_validation->set_rules('codigo', 'Código', 'required|is_unique[cupons.codigo]');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|in_list[percentual,fixo]');
        $this->form_validation->set_rules('valor', 'Valor', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('minimo', 'Valor Mínimo', 'numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('validade', 'Data de Validade', 'callback_validar_data_validade');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('layouts/main', [
                'title' => 'Novo Cupom',
                'contents' => $this->load->view('cupons/form', [], true)
            ]);
            return;
        }

        $validade = $this->input->post('validade');
        $minimo = $this->input->post('minimo');
        
        $data = [
            'codigo' => $this->input->post('codigo'),
            'tipo' => $this->input->post('tipo'),
            'valor' => $this->input->post('valor'),
            'minimo' => !empty($minimo) ? $minimo : 0.00,
            'validade' => !empty($validade) ? $validade : NULL
        ];

        $this->Cupom_model->insert($data);
        redirect('cupom');
    }

    public function edit($id) {
        $cupom = $this->Cupom_model->find($id);
        if (!$cupom) {
            show_404();
        }
        $this->load->view('layouts/main', [
            'title' => 'Editar Cupom',
            'contents' => $this->load->view('cupons/form', ['cupom' => $cupom], true)
        ]);
    }

    public function update($id) {
        $cupom = $this->Cupom_model->find($id);
        if (!$cupom) {
            show_404();
        }

        // Permitindo atualizar o mesmo código do cupom atual, mas bloqueando duplicados para outros registros
        $is_unique = ($this->input->post('codigo') !== $cupom['codigo']) ? '|is_unique[cupons.codigo]' : '';

        $this->form_validation->set_rules('codigo', 'Código', 'required' . $is_unique);
        $this->form_validation->set_rules('tipo', 'Tipo', 'required|in_list[percentual,fixo]');
        $this->form_validation->set_rules('valor', 'Valor', 'required|numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('minimo', 'Valor Mínimo', 'numeric|greater_than_equal_to[0]');
        $this->form_validation->set_rules('validade', 'Data de Validade', 'callback_validar_data_validade');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('layouts/main', [
                'title' => 'Editar Cupom',
                'contents' => $this->load->view('cupons/form', ['cupom' => $cupom], true)
            ]);
            return;
        }

        $validade = $this->input->post('validade');
        $minimo = $this->input->post('minimo');

        $data = [
            'codigo' => $this->input->post('codigo'),
            'tipo' => $this->input->post('tipo'),
            'valor' => $this->input->post('valor'),
            'minimo' => !empty($minimo) ? $minimo : 0.00,
            'validade' => !empty($validade) ? $validade : NULL
        ];

        $this->Cupom_model->update($id, $data);
        redirect('cupom');
    }

    public function delete($id) {
        $this->Cupom_model->delete($id);
        redirect('cupom');
    }

    /**
     * Validação customizada para data de validade
     */
    public function validar_data_validade($data) {
        // Se estiver vazio, é válido (sem validade)
        if (empty($data)) {
            return TRUE;
        }

        // Verificar se é uma data válida
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $this->form_validation->set_message('validar_data_validade', 'O campo {field} deve estar no formato AAAA-MM-DD.');
            return FALSE;
        }

        // Verificar se a data não é no passado
        if (strtotime($data) < strtotime(date('Y-m-d'))) {
            $this->form_validation->set_message('validar_data_validade', 'O campo {field} deve ser uma data futura.');
            return FALSE;
        }

        return TRUE;
    }
}