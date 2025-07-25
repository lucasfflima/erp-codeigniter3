<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Estoque_model extends CI_Model {

    private $table = 'estoque';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Busca estoque de um produto específico
     */
    public function get_by_produto($produto_id, $variacao = null) {
        $this->db->where('produto_id', $produto_id);
        
        // Trata tanto NULL quanto string vazia como sem variação
        if (!empty($variacao)) {
            $this->db->where('variacao', $variacao);
        } else {
            $this->db->where('(variacao IS NULL OR variacao = "")');
        }
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Busca estoque específico por produto e variação
     */
    public function find_by_produto_variacao($produto_id, $variacao = null) {
        $this->db->where('produto_id', $produto_id);
        
        // Trata tanto NULL quanto string vazia como sem variação
        if (empty($variacao)) {
            $this->db->where('(variacao IS NULL OR variacao = "")');
        } else {
            $this->db->where('variacao', $variacao);
        }
        
        return $this->db->get($this->table)->row();
    }

    /**
     * Calcula o total de estoque disponível para um produto
     */
    public function total_estoque_produto($produto_id) {
        $this->db->select('SUM(quantidade) as total');
        $this->db->where('produto_id', $produto_id);
        $result = $this->db->get($this->table)->row();
        
        return $result ? (int)$result->total : 0;
    }

    /**
     * Lista todos os registros de estoque para um produto específico
     */
    public function listar_por_produto($produto_id) {
        $this->db->where('produto_id', $produto_id);
        $this->db->order_by('variacao', 'ASC');
        return $this->db->get($this->table)->result();
    }

    /**
     * Atualiza estoque (alias para set_estoque para compatibilidade)
     */
    public function atualizar_estoque($produto_id, $variacao = null, $quantidade = 0) {
        return $this->set_estoque($produto_id, $quantidade, $variacao);
    }

    /**
     * Cria ou atualiza estoque
     */
    public function set_estoque($produto_id, $quantidade, $variacao = null) {
        $estoque_existente = $this->find_by_produto_variacao($produto_id, $variacao);
        
        if ($estoque_existente) {
            // Atualiza
            $this->db->where('id', $estoque_existente->id);
            return $this->db->update($this->table, [
                'quantidade' => $quantidade,
                'atualizado_em' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Cria novo
            return $this->db->insert($this->table, [
                'produto_id' => $produto_id,
                'variacao' => $variacao,
                'quantidade' => $quantidade
            ]);
        }
    }

    /**
     * Adiciona quantidade ao estoque
     */
    public function adicionar_estoque($produto_id, $quantidade, $variacao = null) {
        $estoque = $this->find_by_produto_variacao($produto_id, $variacao);
        
        if ($estoque) {
            $nova_quantidade = $estoque->quantidade + $quantidade;
            return $this->set_estoque($produto_id, $nova_quantidade, $variacao);
        } else {
            return $this->set_estoque($produto_id, $quantidade, $variacao);
        }
    }

    public function reduzir_estoque($produto_id, $quantidade, $variacao = null) {
        $estoque = $this->find_by_produto_variacao($produto_id, $variacao);
        
        if (!$estoque) {
            return [
                'sucesso' => false,
                'mensagem' => 'Estoque não encontrado',
                'estoque_atual' => 0
            ];
        }
        
       
        $quantidade_disponivel = $estoque->quantidade;
        
        if ($quantidade_disponivel < $quantidade) {
            return [
                'sucesso' => false,
                'mensagem' => 'Estoque insuficiente',
                'estoque_atual' => $quantidade_disponivel
            ];
        }
        
        $nova_quantidade = $estoque->quantidade - $quantidade;
        $sucesso = $this->set_estoque($produto_id, $nova_quantidade, $variacao);
        
        return [
            'sucesso' => $sucesso,
            'mensagem' => $sucesso ? 'Estoque reduzido com sucesso' : 'Erro ao reduzir estoque',
            'estoque_atual' => $nova_quantidade
        ];
    }

    /**
     * Verifica disponibilidade de estoque
     */
    public function verificar_disponibilidade($produto_id, $quantidade, $variacao = null) {
        $estoque = $this->find_by_produto_variacao($produto_id, $variacao);
        
        if (!$estoque) {
            return [
                'disponivel' => false,
                'quantidade_disponivel' => 0,
                'mensagem' => 'Produto sem estoque cadastrado'
            ];
        }
        
        // Como não usamos reservas (carrinho é baseado em sessão), 
        // toda quantidade em estoque está disponível
        $quantidade_disponivel = $estoque->quantidade;
        
        return [
            'disponivel' => $quantidade_disponivel >= $quantidade,
            'quantidade_disponivel' => $quantidade_disponivel,
            'mensagem' => $quantidade_disponivel >= $quantidade ? 'Disponível' : 'Estoque insuficiente'
        ];
    }

    /**
     * Remove todos os estoques de um produto
     */
    public function delete_by_produto($produto_id) {
        return $this->db->where('produto_id', $produto_id)->delete($this->table);
    }
}
