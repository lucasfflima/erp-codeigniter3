<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ItemPedido_model extends CI_Model {

  public function inserir($dados) {
    return $this->db->insert('pedido_itens', $dados);
  }

  public function listarPorPedido($pedido_id) {
    return $this->db->get_where('pedido_itens', ['pedido_id' => $pedido_id])->result_array();
  }

  public function excluirPorPedido($pedido_id) {
    return $this->db->delete('pedido_itens', ['pedido_id' => $pedido_id]);
  }
}
