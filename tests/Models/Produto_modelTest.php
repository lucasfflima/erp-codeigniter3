<?php
/**
 * Testes unitários para o modelo Produto_model
 * 
 * Este arquivo contém testes para verificar a funcionalidade de controle de estoque
 * incluindo cenários de estoque insuficiente, produtos inexistentes e atualizações corretas.
 */
use PHPUnit\Framework\TestCase;

class Produto_modelTest extends TestCase {
    /** @var object Instância do CodeIgniter */
    protected $CI;
    
    /** @var Produto_model Instância do modelo de produtos */
    protected $produto_model;

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
        
        // Carregar o model
        $this->CI->load->model('Produto_model');
        $this->produto_model = $this->CI->Produto_model;
        
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
        
        // Limpar tabela de produtos
        $this->CI->db->empty_table('produtos');
    }
    
    private function inserirDadosTeste() {
        // Produto com estoque normal
        $this->CI->db->insert('produtos', [
            'id' => 1,
            'nome' => 'Produto Normal',
            'preco' => 25.00,
            'estoque' => 100
        ]);
        
        // Produto com estoque baixo
        $this->CI->db->insert('produtos', [
            'id' => 2,
            'nome' => 'Produto Estoque Baixo',
            'preco' => 15.00,
            'estoque' => 5
        ]);
        
        // Produto sem estoque
        $this->CI->db->insert('produtos', [
            'id' => 3,
            'nome' => 'Produto Sem Estoque',
            'preco' => 30.00,
            'estoque' => 0
        ]);
    }

    public function testBaixarEstoqueComSucesso(): void {
        $resultado = $this->produto_model->baixar_estoque(1, 10);
        
        $this->assertTrue($resultado['sucesso'], 'Baixa de estoque deveria ser bem-sucedida');
        $this->assertEquals('Estoque atualizado com sucesso', $resultado['mensagem']);
        $this->assertEquals(90, $resultado['estoque_atual'], 'Estoque deveria ser 90 após baixar 10');
    }
    
    public function testBaixarEstoqueComEstoqueInsuficiente(): void {
        $resultado = $this->produto_model->baixar_estoque(2, 10); // Produto tem apenas 5 em estoque
        
        $this->assertFalse($resultado['sucesso'], 'Baixa de estoque deveria falhar com estoque insuficiente');
        $this->assertEquals('Estoque insuficiente', $resultado['mensagem']);
        $this->assertEquals(5, $resultado['estoque_atual'], 'Estoque deveria permanecer 5');
    }
    
    public function testBaixarEstoqueProdutoInexistente(): void {
        $resultado = $this->produto_model->baixar_estoque(999, 1); // Produto inexistente
        
        $this->assertFalse($resultado['sucesso'], 'Baixa de estoque deveria falhar para produto inexistente');
        $this->assertEquals('Produto não encontrado', $resultado['mensagem']);
        $this->assertEquals(0, $resultado['estoque_atual'], 'Estoque deveria ser 0 para produto inexistente');
    }
    
    public function testBaixarEstoqueComEstoqueZero(): void {
        $resultado = $this->produto_model->baixar_estoque(3, 1); // Produto sem estoque
        
        $this->assertFalse($resultado['sucesso'], 'Baixa de estoque deveria falhar com produto sem estoque');
        $this->assertEquals('Estoque insuficiente', $resultado['mensagem']);
        $this->assertEquals(0, $resultado['estoque_atual'], 'Estoque deveria permanecer 0');
    }
    
    public function testBaixarEstoqueQuantidadeExata(): void {
        $resultado = $this->produto_model->baixar_estoque(2, 5); // Exatamente o estoque disponível
        
        $this->assertTrue($resultado['sucesso'], 'Baixa de estoque deveria ser bem-sucedida com quantidade exata');
        $this->assertEquals('Estoque atualizado com sucesso', $resultado['mensagem']);
        $this->assertEquals(0, $resultado['estoque_atual'], 'Estoque deveria ser 0 após baixar tudo');
    }
    
    public function testReduzirEstoqueDirecto(): void {
        // Teste do método reduzir_estoque diretamente
        $resultado = $this->produto_model->reduzir_estoque(1, 20);
        $this->assertTrue($resultado, 'Redução de estoque deveria ser bem-sucedida');
        
        // Verificar se o estoque foi realmente reduzido
        $produto = $this->produto_model->find(1);
        $this->assertEquals(80, $produto->estoque, 'Estoque deveria ser 80 após reduzir 20');
    }
    
    public function testAumentarEstoque(): void {
        // Teste do método aumentar_estoque
        $resultado = $this->produto_model->aumentar_estoque(2, 15);
        $this->assertTrue($resultado, 'Aumento de estoque deveria ser bem-sucedido');
        
        // Verificar se o estoque foi realmente aumentado
        $produto = $this->produto_model->find(2);
        $this->assertEquals(20, $produto->estoque, 'Estoque deveria ser 20 após aumentar 15');
    }
    
    public function testCRUDBasico(): void {
        // Teste de criação
        $dados_produto = [
            'nome' => 'Produto Teste CRUD',
            'preco' => 50.00,
            'estoque' => 25
        ];
        
        $resultado_insert = $this->produto_model->insert($dados_produto);
        $this->assertTrue($resultado_insert, 'Inserção de produto deveria ser bem-sucedida');
        
        // Buscar o produto inserido
        $produto_id = $this->CI->db->insert_id();
        $produto = $this->produto_model->find($produto_id);
        $this->assertNotNull($produto, 'Produto inserido deveria ser encontrado');
        $this->assertEquals('Produto Teste CRUD', $produto->nome);
        
        // Teste de atualização
        $dados_update = ['preco' => 75.00];
        $resultado_update = $this->produto_model->update($produto_id, $dados_update);
        $this->assertTrue($resultado_update, 'Atualização de produto deveria ser bem-sucedida');
        
        // Verificar atualização
        $produto_atualizado = $this->produto_model->find($produto_id);
        $this->assertEquals(75.00, (float)$produto_atualizado->preco, 'Preço deveria ser atualizado');
        
        // Teste de exclusão
        $resultado_delete = $this->produto_model->delete($produto_id);
        $this->assertTrue($resultado_delete, 'Exclusão de produto deveria ser bem-sucedida');
        
        // Verificar exclusão
        $produto_excluido = $this->produto_model->find($produto_id);
        $this->assertNull($produto_excluido, 'Produto excluído não deveria ser encontrado');
    }
}
