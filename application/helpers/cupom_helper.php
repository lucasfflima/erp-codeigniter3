<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper para aplicação de cupons de desconto
 * 
 * Contém funções utilitárias para calcular descontos e aplicar cupons
 */

if (!function_exists('aplicar_cupom')) {
    /**
     * Aplica desconto de cupom ao valor total
     * 
     * @param array $cupom Dados do cupom
     * @param float $valor_total Valor total do pedido
     * @return array Resultado com valor final e desconto aplicado
     */
    function aplicar_cupom($cupom, $valor_total) {
        if (empty($cupom)) {
            return [
                'valor_final' => $valor_total,
                'desconto' => 0.00,
                'percentual_desconto' => 0.00
            ];
        }
        
        $desconto = 0.00;
        
        if ($cupom['tipo'] === 'percentual') {
            $desconto = $valor_total * ($cupom['valor'] / 100);
        } elseif ($cupom['tipo'] === 'fixo') {
            $desconto = min($cupom['valor'], $valor_total); // Desconto não pode ser maior que o valor total
        }
        
        $valor_final = max(0, $valor_total - $desconto); // Valor final não pode ser negativo
        $percentual_desconto = $valor_total > 0 ? ($desconto / $valor_total) * 100 : 0;
        
        return [
            'valor_final' => round($valor_final, 2),
            'desconto' => round($desconto, 2),
            'percentual_desconto' => round($percentual_desconto, 2)
        ];
    }
}

if (!function_exists('calcular_desconto_percentual')) {
    /**
     * Calcula desconto percentual
     * 
     * @param float $valor Valor base
     * @param float $percentual Percentual de desconto
     * @return float Valor do desconto
     */
    function calcular_desconto_percentual($valor, $percentual) {
        return round($valor * ($percentual / 100), 2);
    }
}

if (!function_exists('calcular_desconto_fixo')) {
    /**
     * Calcula desconto fixo
     * 
     * @param float $valor_total Valor total
     * @param float $desconto_fixo Valor fixo de desconto
     * @return float Valor do desconto (não pode ser maior que o total)
     */
    function calcular_desconto_fixo($valor_total, $desconto_fixo) {
        return round(min($desconto_fixo, $valor_total), 2);
    }
}

if (!function_exists('formatar_desconto')) {
    /**
     * Formata desconto para exibição
     * 
     * @param array $cupom Dados do cupom
     * @return string Desconto formatado para exibição
     */
    function formatar_desconto($cupom) {
        if (empty($cupom)) {
            return '';
        }
        
        if ($cupom['tipo'] === 'percentual') {
            return $cupom['valor'] . '% de desconto';
        } elseif ($cupom['tipo'] === 'fixo') {
            return 'R$ ' . number_format($cupom['valor'], 2, ',', '.') . ' de desconto';
        }
        
        return '';
    }
}

if (!function_exists('validar_valor_minimo_cupom')) {
    /**
     * Valida se o valor do pedido atende ao mínimo do cupom
     * 
     * @param array $cupom Dados do cupom
     * @param float $valor_pedido Valor do pedido
     * @return bool True se atende ao mínimo
     */
    function validar_valor_minimo_cupom($cupom, $valor_pedido) {
        if (empty($cupom)) {
            return false;
        }
        
        return $valor_pedido >= $cupom['minimo'];
    }
}
