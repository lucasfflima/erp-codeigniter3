<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cupom_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_all() {
        return $this->db->order_by('id', 'DESC')->get('cupons')->result_array();
    }

    public function find($id) {
        return $this->db->get_where('cupons', ['id' => $id])->row_array();
    }

    public function insert($data) {
        return $this->db->insert('cupons', $data);
    }

    public function update($id, $data) {
        return $this->db->update('cupons', $data, ['id' => $id]);
    }

    public function delete($id) {
        return $this->db->delete('cupons', ['id' => $id]);
    }
    
    /**
     * Valida se um cupom pode ser usado
     * @param string $codigo Código do cupom
     * @param float $valor_pedido Valor total do pedido
     * @return array Resultado da validação com status e mensagem
     */
    public function validar_cupom($codigo, $valor_pedido = 0) {
        // Buscar cupom pelo código
        $cupom = $this->db->get_where('cupons', ['codigo' => $codigo])->row_array();
        
        if (!$cupom) {
            return [
                'valido' => false,
                'mensagem' => 'Cupom não encontrado',
                'cupom' => null
            ];
        }
        
        // Verificar se o cupom não está expirado
        // Se validade for NULL, considera como sem validade (sempre válido)
        if ($cupom['validade'] && strtotime($cupom['validade']) < time()) {
            return [
                'valido' => false,
                'mensagem' => 'Cupom expirado',
                'cupom' => $cupom
            ];
        }
        
        // Verificar valor mínimo
        if ($valor_pedido < $cupom['minimo']) {
            return [
                'valido' => false,
                'mensagem' => 'Valor mínimo para uso do cupom não atingido',
                'cupom' => $cupom
            ];
        }
        
        // Verificar se o cupom já foi usado (se houver campo usado)
        if (isset($cupom['usado']) && $cupom['usado'] == 1) {
            return [
                'valido' => false,
                'mensagem' => 'Cupom já foi utilizado',
                'cupom' => $cupom
            ];
        }
        
        return [
            'valido' => true,
            'mensagem' => 'Cupom válido',
            'cupom' => $cupom
        ];
    }

    /**
     * Conta o total de cupons cadastrados
     * @return int Total de cupons
     */
    public function contar() {
        return $this->db->count_all('cupons');
    }
}