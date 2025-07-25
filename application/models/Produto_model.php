<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produto_model extends CI_Model {

    private $table = 'produtos';

    public function __construct() {
        parent::__construct();
        $this->load->model('Estoque_model');
    }

    public function get_all() {
        return $this->db->get($this->table)->result();
    }

    /**
     * Busca produto com informações de estoque da tabela separada
     */
    public function get_all_with_estoque() {
        $produtos = $this->get_all();
        
        foreach ($produtos as &$produto) {
            // Busca estoque total da tabela estoque
            $produto->estoque_total = $this->Estoque_model->total_estoque_produto($produto->id);
            
            // Busca detalhes de estoque por variação
            $produto->estoque_detalhes = $this->Estoque_model->listar_por_produto($produto->id);
            
            // Decodifica variações se existirem
            if ($produto->variacoes) {
                $produto->variacoes_array = json_decode($produto->variacoes, true);
            }
        }
        
        return $produtos;
    }

    public function find($id) {
        $produto = $this->db->get_where($this->table, ['id' => $id])->row();
        
        if ($produto) {
            // Usa estoque da própria tabela produtos
            $produto->estoque_total = $produto->estoque ?? 0;
            
            // Decodifica variações se existirem
            if ($produto->variacoes) {
                $produto->variacoes_array = json_decode($produto->variacoes, true);
            }
        }
        
        return $produto;
    }

    /**
     * Lista produtos com filtros opcionais
     */
    public function listar($filtros = []) {
        $this->db->select('produtos.*');
        $this->db->from($this->table);
        
        // Aplicar filtros
        if (!empty($filtros['nome'])) {
            $this->db->like('produtos.nome', $filtros['nome']);
        }
        
        if (!empty($filtros['categoria'])) {
            $this->db->where('produtos.categoria', $filtros['categoria']);
        }
        
        if (!empty($filtros['status'])) {
            if ($filtros['status'] === 'ativo') {
                $this->db->where('produtos.ativo', 1);
            } elseif ($filtros['status'] === 'inativo') {
                $this->db->where('produtos.ativo', 0);
            }
        }
        
        $this->db->order_by('produtos.nome', 'ASC');
        
        $produtos = $this->db->get()->result();
        
        // Adiciona informações de variações decodificadas e estoque da tabela separada
        foreach ($produtos as &$produto) {
            if ($produto->variacoes) {
                $produto->variacoes_array = json_decode($produto->variacoes, true);
            } else {
                $produto->variacoes_array = [];
            }
            
            // Busca estoque total da tabela separada
            $produto->estoque_total = $this->Estoque_model->total_estoque_produto($produto->id);
        }
        
        return $produtos;
    }

    public function insert($data) {
        // Processa variações
        $variacoes = null;
        if (isset($data['variacoes']) && is_array($data['variacoes'])) {
            $variacoes = $data['variacoes'];
            $data['variacoes'] = json_encode($data['variacoes']);
        }
        
        // Extrai estoque inicial para criar na tabela estoque
        $estoque_inicial = $data['estoque_inicial'] ?? 0;
        unset($data['estoque_inicial']);
        
        // Remove campo estoque se existir (não deve estar na tabela produtos)
        if (isset($data['estoque'])) {
            unset($data['estoque']);
        }
        
        // Insere produto
        if ($this->db->insert($this->table, $data)) {
            $produto_id = $this->db->insert_id();
            
            // Cria registros de estoque
            if (!empty($variacoes) && is_array($variacoes)) {
                // Cria estoque para cada variação
                foreach ($variacoes as $variacao) {
                    $this->Estoque_model->atualizar_estoque($produto_id, $variacao, $estoque_inicial);
                }
            } else {
                // Cria estoque padrão (sem variação)
                $this->Estoque_model->atualizar_estoque($produto_id, null, $estoque_inicial);
            }
            
            return $produto_id;
        }
        
        return false;
    }

    public function update($id, $data) {
        // Processa variações
        if (isset($data['variacoes']) && is_array($data['variacoes'])) {
            $data['variacoes'] = json_encode($data['variacoes']);
        }
        
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id) {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function reduzir_estoque($id, $quantidade) {
        $this->db->set('estoque', 'estoque - ' . (int) $quantidade, FALSE);
        $this->db->where('id', $id);
        return $this->db->update($this->table);
    }

    public function aumentar_estoque($id, $quantidade) {
        $this->db->set('estoque', 'estoque + ' . (int) $quantidade, FALSE);
        $this->db->where('id', $id);
        return $this->db->update($this->table);
    }
    
    /**
     * Baixa estoque do produto verificando disponibilidade
     * @param int $id ID do produto
     * @param int $quantidade Quantidade a ser baixada
     * @return array Resultado da operação com status e mensagem
     */
    public function baixar_estoque($id, $quantidade) {
        // Verificar se o produto existe
        $produto = $this->find($id);
        if (!$produto) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado',
                'estoque_atual' => 0
            ];
        }
        
        // Verificar se há estoque suficiente
        if ($produto->estoque < $quantidade) {
            return [
                'sucesso' => false,
                'mensagem' => 'Estoque insuficiente',
                'estoque_atual' => $produto->estoque
            ];
        }
        
        // Reduzir estoque
        $resultado = $this->reduzir_estoque($id, $quantidade);
        
        if ($resultado) {
            // Buscar estoque atualizado
            $produto_atualizado = $this->find($id);
            return [
                'sucesso' => true,
                'mensagem' => 'Estoque atualizado com sucesso',
                'estoque_atual' => $produto_atualizado->estoque
            ];
        } else {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao atualizar estoque',
                'estoque_atual' => $produto->estoque
            ];
        }
    }

    /**
     * Conta o total de produtos cadastrados
     * @return int Total de produtos
     */
    public function contar() {
        return $this->db->count_all($this->table);
    }
}
