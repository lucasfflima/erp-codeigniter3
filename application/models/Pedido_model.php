<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pedido_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function criar_pedido_com_itens($data) {
        $this->db->trans_start();

        $pedido = [
            'cupom_id' => $data['cupom_id'] ?? null,
            'total' => 0,
            'email_cliente' => $data['email_cliente'] ?? null,
            'cep' => $data['cep'] ?? null,
            'endereco_completo' => $data['endereco_completo'] ?? null,
            'cidade' => $data['cidade'] ?? null,
            'estado' => $data['estado'] ?? null,
            'criado_em' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('pedidos', $pedido);
        $pedido_id = $this->db->insert_id();

        $total = 0;

        // Os produtos agora vêm do carrinho como array associativo
        foreach ($data['produtos'] as $chave_item => $item) {
            if ($item['quantidade'] > 0) {
                $subtotal = $item['preco'] * $item['quantidade'];
                $total += $subtotal;

                // Reduz estoque usando o modelo de estoque separado
                $this->load->model('Estoque_model');
                $this->Estoque_model->reduzir_estoque(
                    $item['produto_id'], 
                    $item['quantidade'], 
                    $item['variacao']
                );

                // Insere item do pedido
                $item_pedido = [
                    'pedido_id' => $pedido_id,
                    'produto_id' => $item['produto_id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'variacao' => $item['variacao'] ?? null,
                    'subtotal' => $subtotal
                ];

                $this->db->insert('itens_pedido', $item_pedido);
            }
        }

        // Aplica desconto se houver cupom
        if (!empty($data['cupom_id'])) {
            $cupom = $this->db->get_where('cupons', ['id' => $data['cupom_id']])->row_array();
            if ($cupom) {
                $desconto = $cupom['tipo'] === 'percentual'
                    ? $total * ($cupom['valor'] / 100)
                    : $cupom['valor'];
                $total = max(0, $total - $desconto);
            }
        }

        // Atualiza total final
        $this->db->update('pedidos', ['total' => $total], ['id' => $pedido_id]);

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function get_all_com_relacoes() {
        $this->db->order_by('id', 'DESC');
        $pedidos = $this->db->get('pedidos')->result_array();

        foreach ($pedidos as &$pedido) {
            $pedido['itens'] = $this->db->select('produtos.nome, itens_pedido.quantidade')
                ->from('itens_pedido')
                ->join('produtos', 'produtos.id = itens_pedido.produto_id')
                ->where('itens_pedido.pedido_id', $pedido['id'])
                ->get()
                ->result_array();

            if ($pedido['cupom_id']) {
                $pedido['cupom'] = $this->db->get_where('cupons', ['id' => $pedido['cupom_id']])->row_array();
            } else {
                $pedido['cupom'] = null;
            }
        }

        return $pedidos;
    }

    public function get_pedido_com_itens($id) {
    $pedido = $this->db->where('id', $id)->get('pedidos')->row();

    if (!$pedido) return null;

    // Buscar cupom, se existir
    if ($pedido->cupom_id) {
        $pedido->cupom = $this->db->where('id', $pedido->cupom_id)->get('cupons')->row();
    }

    // Buscar itens e os produtos relacionados
    $itens = $this->db->where('pedido_id', $id)->get('pedido_itens')->result();

    foreach ($itens as &$item) {
        $item->produto = $this->db->where('id', $item->produto_id)->get('produtos')->row();
    }

    $pedido->itens = $itens;

    return $pedido;
}

    /**
     * Alias para get_pedido_com_itens para compatibilidade com controller
     */
    public function get_com_relacoes($id) {
        return $this->get_pedido_com_itens($id);
    }

    /**
     * Conta o total de pedidos cadastrados
     * @return int Total de pedidos
     */
    public function contar() {
        return $this->db->count_all('pedidos');
    }

    /**
     * Soma o valor total de todos os pedidos
     * @return float Total em valor dos pedidos
     */
    public function somarTotal() {
        $this->db->select('SUM(total) as total_geral');
        $result = $this->db->get('pedidos')->row();
        return $result->total_geral ? (float)$result->total_geral : 0;
    }

    /**
     * Busca dados completos do pedido para e-mail
     */
    public function get_pedido_completo($id) {
        // Buscar pedido principal
        $pedido = $this->db->where('id', $id)->get('pedidos')->row_array();
        
        if (!$pedido) {
            return null;
        }
        
        // Buscar itens do pedido
        $this->db->select('ip.*, p.nome, p.preco');
        $this->db->from('itens_pedido ip');
        $this->db->join('produtos p', 'p.id = ip.produto_id');
        $this->db->where('ip.pedido_id', $id);
        $pedido['itens'] = $this->db->get()->result_array();
        
        // Buscar dados do cupom se existir
        if ($pedido['cupom_id']) {
            $pedido['cupom'] = $this->db->where('id', $pedido['cupom_id'])->get('cupons')->row_array();
        }
        
        return $pedido;
    }

    /**
     * Atualiza status do pedido
     */
    public function atualizar_status($id, $novo_status) {
        return $this->db->where('id', $id)->update('pedidos', ['status' => $novo_status]);
    }

    /**
     * Remove pedido e seus itens
     */
    public function remover_pedido($id) {
        $this->db->trans_start();
        
        // Remover itens do pedido
        $this->db->delete('itens_pedido', ['pedido_id' => $id]);
        
        // Remover pedido
        $this->db->delete('pedidos', ['id' => $id]);
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
