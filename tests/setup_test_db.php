<?php
/**
 * Script para configurar o banco de dados de teste
 * Execute este script antes de rodar os testes pela primeira vez
 */

// Configuração do banco
$host = 'localhost';
$username = 'root';
$password = 'password';
$database = 'erp_ci3';
$test_database = 'erp_ci3_test';

try {
    // Conecta ao MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cria o banco de teste se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$test_database` CHARACTER SET utf8 COLLATE utf8_general_ci");
    echo "Banco de dados de teste '$test_database' criado/verificado com sucesso.\n";
    
    // Conecta ao banco de produção para copiar a estrutura
    $pdo_prod = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo_test = new PDO("mysql:host=$host;dbname=$test_database", $username, $password);
    
    // Limpa o banco de teste
    $tables = $pdo_test->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $pdo_test->exec("DROP TABLE `$table`");
    }
    
    // Copia a estrutura das tabelas do banco de produção
    $tables = $pdo_prod->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $create_table = $pdo_prod->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $pdo_test->exec($create_table['Create Table']);
        echo "Tabela '$table' copiada para o banco de teste.\n";
    }
    
    echo "Configuração do banco de teste concluída com sucesso!\n";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
