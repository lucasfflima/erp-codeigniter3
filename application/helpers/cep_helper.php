<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper para integração com ViaCEP
 */

if (!function_exists('buscar_cep')) {
    /**
     * Busca informações de endereço por CEP usando ViaCEP
     * 
     * @param string $cep CEP para buscar (com ou sem formatação)
     * @return array|false Dados do endereço ou false em caso de erro
     */
    function buscar_cep($cep) {
        // Remove formatação do CEP
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        // Valida CEP
        if (strlen($cep) !== 8) {
            return false;
        }
        
        // URL da API ViaCEP
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        
        try {
            // Faz requisição para a API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'ERP-CodeIgniter/1.0');
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            
            if ($http_code !== 200 || !$response) {
                return false;
            }
            
            $data = json_decode($response, true);
            
            // Verifica se CEP é válido (ViaCEP retorna 'erro' para CEPs inválidos)
            if (!$data || isset($data['erro'])) {
                return false;
            }
            
            return [
                'cep' => $data['cep'],
                'logradouro' => $data['logradouro'],
                'complemento' => $data['complemento'],
                'bairro' => $data['bairro'],
                'localidade' => $data['localidade'],
                'uf' => $data['uf'],
                'ibge' => $data['ibge'],
                'gia' => $data['gia'],
                'ddd' => $data['ddd'],
                'siafi' => $data['siafi']
            ];
            
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('formatar_cep')) {
    /**
     * Formata CEP para o padrão XXXXX-XXX
     * 
     * @param string $cep CEP sem formatação
     * @return string CEP formatado
     */
    function formatar_cep($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) === 8) {
            return substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
        
        return $cep;
    }
}

if (!function_exists('validar_cep')) {
    /**
     * Valida formato do CEP
     * 
     * @param string $cep CEP para validar
     * @return bool True se válido, false caso contrário
     */
    function validar_cep($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) === 8 && is_numeric($cep);
    }
}

if (!function_exists('buscar_cep_ajax')) {
    /**
     * Função auxiliar para retornar dados em formato JSON para AJAX
     * 
     * @param string $cep CEP para buscar
     * @return string JSON com dados ou erro
     */
    function buscar_cep_ajax($cep) {
        $dados = buscar_cep($cep);
        
        if ($dados) {
            return json_encode([
                'sucesso' => true,
                'dados' => $dados
            ]);
        } else {
            return json_encode([
                'sucesso' => false,
                'mensagem' => 'CEP não encontrado ou inválido'
            ]);
        }
    }
}
