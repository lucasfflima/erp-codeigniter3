<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carrinho_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Produto_model');
        $this->load->model('Estoque_model');
    }

    /**
     * Adiciona item ao carrinho
     */
    public function adicionar_item($produto_id, $quantidade = 1, $variacao = null) {
        // Verifica se o produto existe
        $produto = $this->Produto_model->find($produto_id);
        if (!$produto) {
            return [
                'sucesso' => false,
                'mensagem' => 'Produto não encontrado'
            ];
        }

        // Processa variação para formato string
        $variacao_processada = $this->processar_variacao($variacao);

        // Verifica disponibilidade no estoque usando tabela estoque
        $disponibilidade = $this->Estoque_model->verificar_disponibilidade($produto_id, $quantidade, $variacao_processada);
        if (!$disponibilidade['disponivel']) {
            return [
                'sucesso' => false,
                'mensagem' => $disponibilidade['mensagem']
            ];
        }

        // Busca carrinho atual da sessão
        $carrinho = $this->session->userdata('carrinho') ?: [];
        
        // Cria chave única para o item (produto + variação)
        $chave_item = $produto_id . '_' . ($variacao_processada ?: 'default');
        
        // Se item já existe, soma quantidade
        if (isset($carrinho[$chave_item])) {
            $nova_quantidade = $carrinho[$chave_item]['quantidade'] + $quantidade;
            
            // Verifica se há estoque suficiente para a nova quantidade
            $disponibilidade_nova = $this->Estoque_model->verificar_disponibilidade($produto_id, $nova_quantidade, $variacao_processada);
            if (!$disponibilidade_nova['disponivel']) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Quantidade solicitada excede o estoque disponível'
                ];
            }
            
            $carrinho[$chave_item]['quantidade'] = $nova_quantidade;
        } else {
            // Adiciona novo item
            $carrinho[$chave_item] = [
                'produto_id' => $produto_id,
                'nome' => $produto->nome,
                'preco' => $produto->preco,
                'quantidade' => $quantidade,
                'variacao' => $variacao_processada,
                'subtotal' => $produto->preco * $quantidade
            ];
        }

        // Atualiza subtotal do item
        $carrinho[$chave_item]['subtotal'] = $carrinho[$chave_item]['preco'] * $carrinho[$chave_item]['quantidade'];

        // Salva carrinho na sessão
        $this->session->set_userdata('carrinho', $carrinho);

        return [
            'sucesso' => true,
            'mensagem' => 'Item adicionado ao carrinho',
            'carrinho' => $carrinho
        ];
    }

    /**
     * Remove item do carrinho
     */
    public function remover_item($chave_item) {
        $carrinho = $this->session->userdata('carrinho') ?: [];
        
        if (isset($carrinho[$chave_item])) {
            unset($carrinho[$chave_item]);
            $this->session->set_userdata('carrinho', $carrinho);
            
            return [
                'sucesso' => true,
                'mensagem' => 'Item removido do carrinho'
            ];
        }
        
        return [
            'sucesso' => false,
            'mensagem' => 'Item não encontrado no carrinho'
        ];
    }

    /**
     * Remove item do carrinho por produto_id e variação
     */
    public function remover_item_produto($produto_id, $variacao = null) {
        $variacao_processada = $this->processar_variacao($variacao);
        $chave_item = $produto_id . '_' . ($variacao_processada ?: 'default');
        
        return $this->remover_item($chave_item);
    }

    /**
     * Atualiza quantidade de um item
     */
    public function atualizar_quantidade($chave_item, $quantidade) {
        $carrinho = $this->session->userdata('carrinho') ?: [];
        
        if (!isset($carrinho[$chave_item])) {
            return [
                'sucesso' => false,
                'mensagem' => 'Item não encontrado no carrinho'
            ];
        }

        $item = $carrinho[$chave_item];
        $produto_id = $item['produto_id'];
        $variacao_processada = $this->processar_variacao($item['variacao']);

        // Verifica disponibilidade no estoque
        $disponibilidade = $this->Estoque_model->verificar_disponibilidade($produto_id, $quantidade, $variacao_processada);
        if (!$disponibilidade['disponivel']) {
            return [
                'sucesso' => false,
                'mensagem' => $disponibilidade['mensagem']
            ];
        }

        // Atualiza quantidade e subtotal
        $carrinho[$chave_item]['quantidade'] = $quantidade;
        $carrinho[$chave_item]['subtotal'] = $carrinho[$chave_item]['preco'] * $quantidade;

        $this->session->set_userdata('carrinho', $carrinho);

        return [
            'sucesso' => true,
            'mensagem' => 'Quantidade atualizada'
        ];
    }

    /**
     * Atualiza quantidade de um item por produto_id e variação
     */
    public function atualizar_quantidade_produto($produto_id, $quantidade, $variacao = null) {
        $variacao_processada = $this->processar_variacao($variacao);
        $chave_item = $produto_id . '_' . ($variacao_processada ?: 'default');
        
        return $this->atualizar_quantidade($chave_item, $quantidade);
    }

    /**
     * Retorna itens do carrinho
     */
    public function get_itens() {
        $carrinho = $this->session->userdata('carrinho') ?: [];
        
        // Adiciona informações de estoque a cada item
        foreach ($carrinho as $chave => &$item) {
            $estoque_info = $this->Estoque_model->verificar_disponibilidade(
                $item['produto_id'], 
                $item['quantidade'], 
                $item['variacao']
            );
            
            $item['estoque_disponivel'] = $estoque_info['quantidade_disponivel'];
            $item['estoque_ok'] = $estoque_info['disponivel'];
        }
        
        return $carrinho;
    }

    /**
     * Limpa o carrinho
     */
    public function limpar() {
        $this->session->unset_userdata('carrinho');
    }

    /**
     * Limpa o carrinho (alias para compatibilidade)
     */
    public function limpar_carrinho() {
        $this->limpar();
        return [
            'sucesso' => true,
            'mensagem' => 'Carrinho limpo com sucesso'
        ];
    }

    /**
     * Verifica se o carrinho está vazio
     */
    public function esta_vazio() {
        return !$this->tem_itens();
    }

    /**
     * Calcula subtotal do carrinho
     */
    public function get_subtotal() {
        $carrinho = $this->get_itens();
        $subtotal = 0;
        
        foreach ($carrinho as $item) {
            $subtotal += $item['subtotal'];
        }
        
        return $subtotal;
    }

    /**
     * Retorna quantidade total de itens
     */
    public function get_quantidade_total() {
        $carrinho = $this->get_itens();
        $total = 0;
        
        foreach ($carrinho as $item) {
            $total += $item['quantidade'];
        }
        
        return $total;
    }

    /**
     * Verifica se há itens no carrinho
     */
    public function tem_itens() {
        $carrinho = $this->get_itens();
        return !empty($carrinho);
    }

    /**
     * Conta total de itens únicos no carrinho
     */
    public function contar_itens() {
        $carrinho = $this->get_itens();
        return count($carrinho);
    }

    /**
     * Valida estoque dos itens do carrinho
     */
    public function validar_estoque() {
        $carrinho = $this->get_itens();
        $erros = [];
        
        foreach ($carrinho as $chave => $item) {
            $estoque_info = $this->Estoque_model->verificar_disponibilidade(
                $item['produto_id'], 
                $item['quantidade'], 
                $item['variacao']
            );
            
            if (!$estoque_info['disponivel']) {
                $erros[$chave] = [
                    'produto' => $item['nome'],
                    'mensagem' => $estoque_info['mensagem'],
                    'disponivel' => $estoque_info['quantidade_disponivel']
                ];
            }
        }
        
        return $erros;
    }

    /**
     * Calcula frete baseado no subtotal
     */
    public function calcular_frete($subtotal) {
        // Frete grátis acima de R$ 200
        if ($subtotal >= 200) {
            return 0;
        }
        
        // Frete fixo de R$ 15
        return 15.00;
    }

    /**
     * Aplica cupom de desconto
     */
    public function aplicar_cupom($codigo_cupom) {
        $this->load->model('Cupom_model');
        
        // Obter valor atual do carrinho para validar cupom
        $subtotal = $this->get_subtotal();
        $cupom = $this->Cupom_model->validar_cupom($codigo_cupom, $subtotal);
        
        if ($cupom['valido']) {
            $this->session->set_userdata('cupom_aplicado', $cupom['cupom']);
            return [
                'sucesso' => true,
                'mensagem' => 'Cupom aplicado com sucesso!',
                'desconto' => $cupom['desconto'] ?? 0
            ];
        }
        
        return [
            'sucesso' => false,
            'mensagem' => $cupom['mensagem']
        ];
    }

    /**
     * Remove cupom aplicado
     */
    public function remover_cupom() {
        $this->session->unset_userdata('cupom_aplicado');
        return [
            'sucesso' => true,
            'mensagem' => 'Cupom removido'
        ];
    }

    /**
     * Retorna ID do cupom aplicado
     */
    public function get_cupom_id() {
        $cupom = $this->session->userdata('cupom_aplicado');
        
        if (!$cupom) {
            return null;
        }
        
        // Trata tanto objeto quanto array
        if (is_object($cupom)) {
            return $cupom->id ?? null;
        } elseif (is_array($cupom)) {
            return $cupom['id'] ?? null;
        }
        
        return null;
    }

    /**
     * Calcula desconto do cupom
     */
    public function calcular_desconto_cupom() {
        $cupom = $this->session->userdata('cupom_aplicado');
        
        if (!$cupom) {
            return 0;
        }
        
        // Converte para array se for objeto
        if (is_object($cupom)) {
            $cupom = (array)$cupom;
        }
        
        $subtotal = $this->get_subtotal();
        
        if (isset($cupom['tipo']) && $cupom['tipo'] === 'percentual') {
            $desconto = ($subtotal * $cupom['valor']) / 100;
            
            // Aplica valor máximo se definido
            if (isset($cupom['valor_maximo']) && $cupom['valor_maximo'] && $desconto > $cupom['valor_maximo']) {
                $desconto = $cupom['valor_maximo'];
            }
            
            return $desconto;
        } else {
            // Desconto fixo
            return min($cupom['valor'] ?? 0, $subtotal);
        }
    }

    /**
     * Calcula total com frete e desconto
     */
    public function get_total() {
        $subtotal = $this->get_subtotal();
        $frete = $this->calcular_frete($subtotal);
        $desconto = $this->calcular_desconto_cupom();
        
        return max(0, $subtotal + $frete - $desconto);
    }

    /**
     * CEP para cálculo de frete
     */
    public function definir_cep($cep) {
        $this->session->set_userdata('cep_entrega', $cep);
    }

    /**
     * Busca endereço pelo CEP
     */
    public function buscar_endereco_por_cep($cep) {
        // Remove formatação do CEP
        $cep = preg_replace('/\D/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return [
                'sucesso' => false,
                'mensagem' => 'CEP deve ter 8 dígitos'
            ];
        }
        
        // Consulta ViaCEP
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao consultar CEP'
            ];
        }
        
        $dados = json_decode($response, true);
        
        if (isset($dados['erro'])) {
            return [
                'sucesso' => false,
                'mensagem' => 'CEP não encontrado'
            ];
        }
        
        return [
            'sucesso' => true,
            'endereco' => $dados
        ];
    }

    /**
     * Finalizar compra (reserva estoque)
     */
    public function finalizar_compra($dados_entrega) {
        $carrinho = $this->get_itens();
        
        if (empty($carrinho)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Carrinho vazio'
            ];
        }
        
        // Valida estoque antes de finalizar
        $erros_estoque = $this->validar_estoque();
        if (!empty($erros_estoque)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Alguns itens não estão mais disponíveis',
                'erros' => $erros_estoque
            ];
        }
        
        // Aqui você implementaria a lógica de:
        // 1. Criar pedido na base de dados
        // 2. Reservar estoque
        // 3. Processar pagamento
        // 4. Enviar emails de confirmação
        
        return [
            'sucesso' => true,
            'mensagem' => 'Compra finalizada com sucesso!',
            'pedido_id' => uniqid('PED_')
        ];
    }

    /**
     * Retorna totais com cupom aplicado
     */
    public function get_total_com_cupom() {
        $subtotal = $this->get_subtotal();
        $frete = $this->calcular_frete($subtotal);
        $desconto = $this->calcular_desconto_cupom();
        $total = $subtotal + $frete - $desconto;
        
        return [
            'subtotal' => $subtotal,
            'frete' => $frete,
            'desconto' => $desconto,
            'total' => max(0, $total), // Total não pode ser negativo
            'cupom' => $this->session->userdata('cupom_aplicado')
        ];
    }

    /**
     * Processa variação do formulário para formato string
     */
    private function processar_variacao($variacao) {
        if (empty($variacao)) {
            return null;
        }
        
        // Se for array, converte para string no formato chave:valor,chave:valor
        if (is_array($variacao)) {
            $partes = [];
            foreach ($variacao as $chave => $valor) {
                if (!empty($valor)) {
                    $partes[] = $chave . ':' . $valor;
                }
            }
            return empty($partes) ? null : implode(',', $partes);
        }
        
        return $variacao;
    }
}
