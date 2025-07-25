<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('url');
        $this->load->library('session');
    }

    public function login() {
        if ($this->session->userdata('usuario_logado')) {
            redirect('dashboard');
        }
        $this->load->view('auth/login');
    }

    public function logout() {
        $this->session->unset_userdata('usuario_logado');
        redirect('auth/login');
    }

    public function google_login() {
        $this->load->library('Googleoauth');
        $url = $this->googleoauth->getAuthorizationUrl();
        $this->session->set_userdata('oauth2state', $this->googleoauth->getState());
        redirect($url);
    }

    public function google_callback() {
        $this->load->library('Googleoauth');
        $this->load->model('Usuario_model');

        $state = $this->input->get('state');
        if (!$state || $state !== $this->session->userdata('oauth2state')) {
            show_error('Estado inválido');
        }

        $code = $this->input->get('code');
        if (!$code) {
            show_error('Código de autorização ausente');
        }

        try {
            $token = $this->googleoauth->getAccessToken($code);
            $owner = $this->googleoauth->getResourceOwner($token);
            $userData = $owner->toArray();

            // Cria ou atualiza usuário
            $user = $this->Usuario_model->findOrCreateFromGoogle($userData);

            // Grava usuário na sessão
            $this->session->set_userdata('usuario_logado', [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'foto' => $user['foto'] ?? null
            ]);

            redirect('dashboard');
        } catch (Exception $e) {
            show_error('Erro no login: ' . $e->getMessage());
        }
    }
}