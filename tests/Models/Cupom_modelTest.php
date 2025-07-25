<?php
/**
 * Testes unitários para o modelo Cupom_model
 * 
 * Este arquivo contém testes para verificar a funcionalidade de validação de cupons
 * incluindo cenários de cupons expirados, já utilizados e com valor mínimo.
 */
use PHPUnit\Framework\TestCase;

class Cupom_modelTest extends TestCase {
    /** @var object Instância do CodeIgniter */
    protected $CI;
    
    /** @var Cupom_model Instância do modelo de cupons */
    protected $cupom_model;

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
        $this->CI->load->model('Cupom_model');
        $this->cupom_model = $this->CI->Cupom_model;
        
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
        
        // Limpar tabela de cupons
        $this->CI->db->empty_table('cupons');
    }
    
    private function inserirDadosTeste() {
        // Cupom válido
        $this->CI->db->insert('cupons', [
            'id' => 1,
            'codigo' => 'VALIDO10',
            'tipo' => 'percentual',
            'valor' => 10.00,
            'validade' => date('Y-m-d', strtotime('+30 days')),
            'minimo' => 50.00
        ]);
        
        // Cupom expirado
        $this->CI->db->insert('cupons', [
            'id' => 2,
            'codigo' => 'EXPIRADO20',
            'tipo' => 'percentual',
            'valor' => 20.00,
            'validade' => date('Y-m-d', strtotime('-1 day')),
            'minimo' => 0.00
        ]);
        
        // Cupom com valor mínimo alto
        $this->CI->db->insert('cupons', [
            'id' => 3,
            'codigo' => 'MINIMO100',
            'tipo' => 'fixo',
            'valor' => 15.00,
            'validade' => date('Y-m-d', strtotime('+30 days')),
            'minimo' => 100.00
        ]);
    }

    public function testValidarCupomValido(): void {
        $resultado = $this->cupom_model->validar_cupom('VALIDO10', 100.00);
        
        $this->assertTrue($resultado['valido'], 'Cupom deveria ser válido');
        $this->assertEquals('Cupom válido', $resultado['mensagem']);
        $this->assertNotNull($resultado['cupom'], 'Dados do cupom deveriam estar presentes');
        $this->assertEquals('VALIDO10', $resultado['cupom']['codigo']);
    }
    
    public function testValidarCupomInexistente(): void {
        $resultado = $this->cupom_model->validar_cupom('INEXISTENTE', 100.00);
        
        $this->assertFalse($resultado['valido'], 'Cupom inexistente deveria ser inválido');
        $this->assertEquals('Cupom não encontrado', $resultado['mensagem']);
        $this->assertNull($resultado['cupom'], 'Dados do cupom deveriam ser null');
    }
    
    public function testValidarCupomExpirado(): void {
        $resultado = $this->cupom_model->validar_cupom('EXPIRADO20', 100.00);
        
        $this->assertFalse($resultado['valido'], 'Cupom expirado deveria ser inválido');
        $this->assertEquals('Cupom expirado', $resultado['mensagem']);
        $this->assertNotNull($resultado['cupom'], 'Dados do cupom deveriam estar presentes');
    }
    
    public function testValidarCupomValorMinimoNaoAtingido(): void {
        $resultado = $this->cupom_model->validar_cupom('VALIDO10', 30.00); // Valor menor que o mínimo (50.00)
        
        $this->assertFalse($resultado['valido'], 'Cupom deveria ser inválido por valor mínimo');
        $this->assertEquals('Valor mínimo para uso do cupom não atingido', $resultado['mensagem']);
        $this->assertNotNull($resultado['cupom'], 'Dados do cupom deveriam estar presentes');
    }
    
    public function testValidarCupomComValorMinimoAtingido(): void {
        $resultado = $this->cupom_model->validar_cupom('MINIMO100', 150.00); // Valor maior que o mínimo (100.00)
        
        $this->assertTrue($resultado['valido'], 'Cupom deveria ser válido com valor mínimo atingido');
        $this->assertEquals('Cupom válido', $resultado['mensagem']);
        $this->assertNotNull($resultado['cupom'], 'Dados do cupom deveriam estar presentes');
    }
    
    public function testValidarCupomSemValorMinimo(): void {
        $resultado = $this->cupom_model->validar_cupom('EXPIRADO20', 1.00); // Teste com cupom sem valor mínimo
        
        // Mesmo sendo um valor baixo, o cupom falha por estar expirado, não pelo valor
        $this->assertFalse($resultado['valido'], 'Cupom deveria ser inválido por estar expirado');
        $this->assertEquals('Cupom expirado', $resultado['mensagem']);
    }
}
