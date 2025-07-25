<?php
/**
 * Testes unitários para o modelo Pedido_model
 * 
 * Este arquivo contém testes para verificar a funcionalidade de criação de pedidos
 * incluindo aplicação de cupons de desconto e controle de estoque.
 */
use PHPUnit\Framework\TestCase;

class Pedido_modelTest extends TestCase {
    /** @var object Instância do CodeIgniter */
    protected $CI;
    
    /** @var Pedido_model Instância do modelo de pedidos */
    protected $pedido_model;

    /**
     * Configuração inicial antes de cada teste
     * Inicializa o ambiente CodeIgniter e prepara dados de teste
     */
    protected function setUp(): void {
        // Instancia o CodeIgniter para poder carregar models/helpers/etc
        $this->CI =& get_instance();
        
        // Verificar se a conexão com o banco está funcionando
        if (!$this->CI->db->conn_id) {
            $this->markTestSkipped('Conexão com banco de dados não disponível');
        }
        
        // Carregar o loader
        if (!isset($this->CI->load)) {
            $this->CI->load =& load_class('Loader', 'core');
            $this->CI->load->initialize();
        }
        
        // Carregar o model (nomenclatura corrigida)
        $this->CI->load->model('Pedido_model');
        $this->pedido_model = $this->CI->Pedido_model;
        
        // Limpar dados de teste
        $this->limparDadosTeste();
        
        // Inserir dados de teste básicos
        $this->inserirDadosTeste();
    }
    
    /**
     * Limpeza após cada teste
     * Remove dados de teste para evitar interferência entre testes
     */
    protected function tearDown(): void {
        // Limpar dados após cada teste
        $this->limparDadosTeste();
    }
    
    private function limparDadosTeste() {
        // Verificar se a conexão está ativa antes de executar queries
        if (!$this->CI->db->conn_id) {
            return;
        }
        
        // Desabilitar verificações de foreign key temporariamente
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS = 0');
        
        // Limpar tabelas em ordem para evitar problemas de chave estrangeira
        $this->CI->db->empty_table('itens_pedido');
        $this->CI->db->empty_table('pedidos');
        $this->CI->db->empty_table('produtos');
        $this->CI->db->empty_table('cupons');
        
        // Reabilitar verificações de foreign key
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }
    
    private function inserirDadosTeste() {
        // Inserir produto de teste
        $this->CI->db->insert('produtos', [
            'id' => 1,
            'nome' => 'Produto Teste',
            'preco' => 10.00,
            'estoque' => 100
        ]);
        
        // Inserir cupom de teste
        $this->CI->db->insert('cupons', [
            'id' => 1,
            'codigo' => 'TESTE10',
            'tipo' => 'percentual',
            'valor' => 10.00,
            'validade' => date('Y-m-d', strtotime('+30 days')),
            'minimo' => 0.00
        ]);
    }

    public function testCriarPedidoComItens(): void {
        // Dados simulados para o pedido: 1 produto com quantidade 1, sem cupom
        $data = [
            'produtos' => [1 => 1],
            'cupom_id' => null
        ];

        $result = $this->pedido_model->criar_pedido_com_itens($data);
        $this->assertTrue($result, 'Pedido deveria ser criado com sucesso');
        
        // Verificar se o pedido foi criado
        $pedido = $this->CI->db->get('pedidos')->row_array();
        $this->assertNotNull($pedido, 'Pedido deveria existir no banco');
        $this->assertEquals(10.00, (float)$pedido['total'], 'Total do pedido deveria ser 10.00');
        
        // Verificar se o item do pedido foi criado
        $item = $this->CI->db->get('itens_pedido')->row_array();
        $this->assertNotNull($item, 'Item do pedido deveria existir no banco');
        $this->assertEquals(1, $item['quantidade'], 'Quantidade do item deveria ser 1');
        $this->assertEquals(10.00, (float)$item['preco_unitario'], 'Preço unitário deveria ser 10.00');
    }
    
    public function testCriarPedidoComCupom(): void {
        // Dados simulados para o pedido: 1 produto com quantidade 1, com cupom de 10%
        $data = [
            'produtos' => [1 => 1],
            'cupom_id' => 1
        ];

        $result = $this->pedido_model->criar_pedido_com_itens($data);
        $this->assertTrue($result, 'Pedido com cupom deveria ser criado com sucesso');
        
        // Verificar se o pedido foi criado com desconto aplicado
        $pedido = $this->CI->db->get('pedidos')->row_array();
        $this->assertNotNull($pedido, 'Pedido deveria existir no banco');
        $this->assertEquals(9.00, (float)$pedido['total'], 'Total do pedido deveria ser 9.00 (10% de desconto)');
        $this->assertEquals(1, $pedido['cupom_id'], 'Cupom deveria estar associado ao pedido');
    }
    
    public function testCriarPedidoComQuantidadeMultipla(): void {
        // Dados simulados para o pedido: 1 produto com quantidade 3
        $data = [
            'produtos' => [1 => 3],
            'cupom_id' => null
        ];

        $result = $this->pedido_model->criar_pedido_com_itens($data);
        $this->assertTrue($result, 'Pedido com múltiplos itens deveria ser criado com sucesso');
        
        // Verificar se o pedido foi criado
        $pedido = $this->CI->db->get('pedidos')->row_array();
        $this->assertNotNull($pedido, 'Pedido deveria existir no banco');
        $this->assertEquals(30.00, (float)$pedido['total'], 'Total do pedido deveria ser 30.00 (3 x 10.00)');
        
        // Verificar se o estoque foi reduzido
        $produto = $this->CI->db->get_where('produtos', ['id' => 1])->row_array();
        $this->assertEquals(97, $produto['estoque'], 'Estoque deveria ser reduzido para 97');
    }
    
    public function testCriarPedidoComDadosInvalidos(): void {
        // Teste com dados vazios - o model ainda cria o pedido mas sem itens
        $data = [
            'produtos' => [],
            'cupom_id' => null
        ];

        $result = $this->pedido_model->criar_pedido_com_itens($data);
        $this->assertTrue($result, 'Operação deveria ser bem-sucedida mesmo sem produtos');
        
        // Verificar se o pedido foi criado mas sem itens
        $pedido = $this->CI->db->get('pedidos')->row_array();
        $this->assertNotNull($pedido, 'Pedido deveria ser criado');
        $this->assertEquals(0.00, (float)$pedido['total'], 'Total deveria ser 0.00 sem produtos');
        
        // Verificar se nenhum item foi criado
        $count_itens = $this->CI->db->count_all('itens_pedido');
        $this->assertEquals(0, $count_itens, 'Nenhum item deveria ser criado');
    }
}