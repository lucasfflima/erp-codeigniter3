<?php
/**
 * Testes unitários para o helper cupom_helper
 * 
 * Este arquivo contém testes para verificar a funcionalidade de aplicação de cupons
 * incluindo cálculos de desconto percentual, fixo e validações.
 */
use PHPUnit\Framework\TestCase;

class Cupom_helperTest extends TestCase {
    /** @var object Instância do CodeIgniter */
    protected $CI;

    /**
     * Configuração inicial antes de cada teste
     */
    protected function setUp(): void {
        // Instancia o CodeIgniter para poder carregar helpers
        $this->CI =& get_instance();
        
        // Carregar o loader
        if (!isset($this->CI->load)) {
            $this->CI->load =& load_class('Loader', 'core');
            $this->CI->load->initialize();
        }
        
        // Carregar o helper de cupom
        $this->CI->load->helper('cupom');
    }

    public function testAplicarCupomPercentual(): void {
        $cupom = [
            'tipo' => 'percentual',
            'valor' => 10.00 // 10%
        ];
        
        $resultado = aplicar_cupom($cupom, 100.00);
        
        $this->assertEquals(90.00, $resultado['valor_final'], 'Valor final deveria ser 90.00 com 10% de desconto');
        $this->assertEquals(10.00, $resultado['desconto'], 'Desconto deveria ser 10.00');
        $this->assertEquals(10.00, $resultado['percentual_desconto'], 'Percentual de desconto deveria ser 10%');
    }
    
    public function testAplicarCupomFixo(): void {
        $cupom = [
            'tipo' => 'fixo',
            'valor' => 15.00 // R$ 15,00 fixo
        ];
        
        $resultado = aplicar_cupom($cupom, 100.00);
        
        $this->assertEquals(85.00, $resultado['valor_final'], 'Valor final deveria ser 85.00 com R$ 15,00 de desconto');
        $this->assertEquals(15.00, $resultado['desconto'], 'Desconto deveria ser 15.00');
        $this->assertEquals(15.00, $resultado['percentual_desconto'], 'Percentual de desconto deveria ser 15%');
    }
    
    public function testAplicarCupomFixoMaiorQueTotal(): void {
        $cupom = [
            'tipo' => 'fixo',
            'valor' => 150.00 // R$ 150,00 fixo (maior que o total)
        ];
        
        $resultado = aplicar_cupom($cupom, 100.00);
        
        $this->assertEquals(0.00, $resultado['valor_final'], 'Valor final não pode ser negativo');
        $this->assertEquals(100.00, $resultado['desconto'], 'Desconto deveria ser limitado ao valor total');
        $this->assertEquals(100.00, $resultado['percentual_desconto'], 'Percentual de desconto deveria ser 100%');
    }
    
    public function testAplicarCupomVazio(): void {
        $resultado = aplicar_cupom([], 100.00);
        
        $this->assertEquals(100.00, $resultado['valor_final'], 'Valor final deveria permanecer 100.00 sem cupom');
        $this->assertEquals(0.00, $resultado['desconto'], 'Desconto deveria ser 0.00 sem cupom');
        $this->assertEquals(0.00, $resultado['percentual_desconto'], 'Percentual deveria ser 0% sem cupom');
    }
    
    public function testAplicarCupomNull(): void {
        $resultado = aplicar_cupom(null, 100.00);
        
        $this->assertEquals(100.00, $resultado['valor_final'], 'Valor final deveria permanecer 100.00 com cupom null');
        $this->assertEquals(0.00, $resultado['desconto'], 'Desconto deveria ser 0.00 com cupom null');
        $this->assertEquals(0.00, $resultado['percentual_desconto'], 'Percentual deveria ser 0% com cupom null');
    }
    
    public function testCalcularDescontoPercentual(): void {
        $desconto = calcular_desconto_percentual(200.00, 25.00); // 25% de R$ 200,00
        
        $this->assertEquals(50.00, $desconto, 'Desconto de 25% sobre R$ 200,00 deveria ser R$ 50,00');
    }
    
    public function testCalcularDescontoFixo(): void {
        $desconto = calcular_desconto_fixo(100.00, 30.00);
        
        $this->assertEquals(30.00, $desconto, 'Desconto fixo de R$ 30,00 deveria ser R$ 30,00');
    }
    
    public function testCalcularDescontoFixoMaiorQueTotal(): void {
        $desconto = calcular_desconto_fixo(50.00, 75.00);
        
        $this->assertEquals(50.00, $desconto, 'Desconto fixo não pode ser maior que o valor total');
    }
    
    public function testFormatarDescontoPercentual(): void {
        $cupom = [
            'tipo' => 'percentual',
            'valor' => 15.00
        ];
        
        $formato = formatar_desconto($cupom);
        
        $this->assertEquals('15% de desconto', $formato, 'Formato de desconto percentual incorreto');
    }
    
    public function testFormatarDescontoFixo(): void {
        $cupom = [
            'tipo' => 'fixo',
            'valor' => 25.50
        ];
        
        $formato = formatar_desconto($cupom);
        
        $this->assertEquals('R$ 25,50 de desconto', $formato, 'Formato de desconto fixo incorreto');
    }
    
    public function testFormatarDescontoVazio(): void {
        $formato = formatar_desconto([]);
        
        $this->assertEquals('', $formato, 'Formato de desconto vazio deveria retornar string vazia');
    }
    
    public function testValidarValorMinimoCupomAtendido(): void {
        $cupom = [
            'minimo' => 50.00
        ];
        
        $valido = validar_valor_minimo_cupom($cupom, 75.00);
        
        $this->assertTrue($valido, 'Valor de R$ 75,00 deveria atender ao mínimo de R$ 50,00');
    }
    
    public function testValidarValorMinimoCupomNaoAtendido(): void {
        $cupom = [
            'minimo' => 100.00
        ];
        
        $valido = validar_valor_minimo_cupom($cupom, 75.00);
        
        $this->assertFalse($valido, 'Valor de R$ 75,00 não deveria atender ao mínimo de R$ 100,00');
    }
    
    public function testValidarValorMinimoCupomExato(): void {
        $cupom = [
            'minimo' => 50.00
        ];
        
        $valido = validar_valor_minimo_cupom($cupom, 50.00);
        
        $this->assertTrue($valido, 'Valor exato do mínimo deveria ser válido');
    }
    
    public function testAplicarCupomComValoresComDecimais(): void {
        $cupom = [
            'tipo' => 'percentual',
            'valor' => 12.5 // 12.5%
        ];
        
        $resultado = aplicar_cupom($cupom, 80.75);
        
        $this->assertEquals(70.66, $resultado['valor_final'], 'Cálculo com decimais deveria ser preciso');
        $this->assertEquals(10.09, $resultado['desconto'], 'Desconto com decimais deveria ser preciso');
    }
}
