<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Helper específico para funcionalidades de pedidos
 * Renomeado de form_helper.php para evitar conflito com o helper padrão do CodeIgniter
 */

if ( ! function_exists('preparar_dados_formulario_pedido')) {
  function preparar_dados_formulario_pedido($id = null) {
    $CI =& get_instance();
    $CI->load->model('Produto_model'); // Corrigido nome do modelo
    $CI->load->model('Pedido_model');  // Corrigido nome do modelo

    $data['produtos'] = $CI->Produto_model->get_all(); // Corrigido método

    if ($id) {
      $data['pedido'] = $CI->Pedido_model->find($id); // Corrigido método
    }

    return $data;
  }
}
