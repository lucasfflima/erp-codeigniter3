<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function usuario_logado() {
    $CI =& get_instance();
    return $CI->session->userdata('usuario_logado');
}

function exigir_login() {
    if (!usuario_logado()) {
        redirect('auth/google_login');
    }
}