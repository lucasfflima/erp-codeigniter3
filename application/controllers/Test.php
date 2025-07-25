<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Produto_model');
    }

    /**
     * Testa o método listar() sem autenticação
     */
    public function produto_listar() {
        echo "<h1>Teste do método Produto_model->listar()</h1>";
        
        try {
            $produtos = $this->Produto_model->listar();
            echo "<h2>✅ Método funciona! Produtos encontrados: " . count($produtos) . "</h2>";
            
            if (!empty($produtos)) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
                echo "<tr><th>ID</th><th>Nome</th><th>Preço</th><th>Estoque Total</th><th>Status</th></tr>";
                
                foreach ($produtos as $produto) {
                    $status = $produto->ativo ? 'Ativo' : 'Inativo';
                    echo "<tr>";
                    echo "<td>{$produto->id}</td>";
                    echo "<td>{$produto->nome}</td>";
                    echo "<td>R$ " . number_format($produto->preco, 2, ',', '.') . "</td>";
                    echo "<td>{$produto->estoque_total}</td>";
                    echo "<td>{$status}</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>Nenhum produto cadastrado ainda.</p>";
            }
            
        } catch (Exception $e) {
            echo "<h2>❌ Erro: " . $e->getMessage() . "</h2>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        echo "<hr>";
        echo "<p><a href='/index.php/test/produto_inserir'>Testar inserção de produto</a></p>";
        echo "<p><a href='/index.php/test/produto_controller'>Testar controller Produto</a></p>";
        echo "<p><a href='/index.php/auth/google_login'>Fazer Login</a></p>";
    }

    /**
     * Testa o controller Produto diretamente
     */
    public function produto_controller() {
        echo "<h1>Teste do Controller Produto</h1>";
        
        try {
            // Carrega models como no controller original
            $this->load->model('Produto_model');
            $this->load->model('Estoque_model');
            $this->load->model('Carrinho_model');
            
            echo "<h2>✅ Models carregados com sucesso!</h2>";
            
            // Testa método listar
            $filtros = [
                'nome' => null,
                'categoria' => null,
                'status' => null
            ];
            
            $produtos = $this->Produto_model->listar($filtros);
            echo "<h2>✅ Método listar() funcionou! Produtos: " . count($produtos) . "</h2>";
            
            // Testa contador do carrinho
            $count = $this->Carrinho_model->contar_itens();
            echo "<h2>✅ Contador do carrinho funcionou! Itens: {$count}</h2>";
            
        } catch (Exception $e) {
            echo "<h2>❌ Erro: " . $e->getMessage() . "</h2>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
        echo "<p><a href='/index.php/test/produto_listar'>Voltar</a></p>";
    }

    /**
     * Insere um produto de teste
     */
    public function produto_inserir() {
        echo "<h1>Teste de inserção de produto</h1>";
        
        try {
            $data = [
                'nome' => 'Produto Teste ' . date('H:i:s'),
                'descricao' => 'Produto criado para teste em ' . date('d/m/Y H:i:s'),
                'preco' => 99.99,
                'ativo' => 1,
                'variacoes' => json_encode(['cor' => 'azul', 'tamanho' => 'M']),
                'estoque_inicial' => 10
            ];
            
            if ($this->Produto_model->insert($data)) {
                echo "<h2>✅ Produto inserido com sucesso!</h2>";
                echo "<p><a href='/index.php/test/produto_listar'>Ver todos os produtos</a></p>";
            } else {
                echo "<h2>❌ Erro ao inserir produto</h2>";
            }
            
        } catch (Exception $e) {
            echo "<h2>❌ Erro: " . $e->getMessage() . "</h2>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }

    /**
     * Testa conexão com banco
     */
    public function db_test() {
        echo "<h1>Teste de Conexão com Banco</h1>";
        
        try {
            $query = $this->db->query("SHOW TABLES");
            $tables = $query->result_array();
            
            echo "<h2>✅ Conectado! Tabelas encontradas:</h2>";
            echo "<ul>";
            foreach ($tables as $table) {
                $table_name = current($table);
                echo "<li>{$table_name}</li>";
            }
            echo "</ul>";
            
        } catch (Exception $e) {
            echo "<h2>❌ Erro de conexão: " . $e->getMessage() . "</h2>";
        }
        
        echo "<p><a href='/index.php/test/produto_listar'>Testar produtos</a></p>";
    }
}
