# 🏢 ERP CodeIgniter 3 

[![PHP Version](https://img.shields**Integrações:** Google OAuth 2.0 • ViaCEP API • Gmail SMTP

## ⚙️ Pré-requisitos
- PHP 8.1+ instalado
- MySQL 5.7+ instalado e rodando
- Composer instalado

## 🏃‍♂️ Como Executar/badge/PHP-8.1+-blue.svg)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3.1.13-red.svg)](https://codeigniter.com/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-9.6+-green.svg)](https://phpunit.de/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://www.mysql.com/)

Sistema ERP desenvolvido em CodeIgniter 3 com funcionalidades completas de e-commerce, incluindo carrinho de compras, gestão de pedidos, controle de estoque por variação, sistema de cupons de desconto e notificações por email. Implementado com Google OAuth 2.0, integração com APIs externas e interface responsiva.

## 📋 Índice

- [🎯 Sobre o Projeto](#-sobre-o-projeto)
- [🚀 Funcionalidades](#-funcionalidades)
- [🛠️ Tecnologias](#️-tecnologias)
- [⚙️ Pré-requisitos](#️-pré-requisitos)
- [🏃‍♂️ Como Executar](#️-como-executar)
- [🧪 Teste de Email](#-teste-de-email)

## 🎯 Sobre o Projeto

Sistema ERP completo desenvolvido em CodeIgniter 3 com:

- **E-commerce Completo**: Carrinho de compras com variações de produto
- **Gestão de Pedidos**: Criação e controle com confirmação por email automática
- **Controle de Estoque**: Validação automática por variação de produto
- **Sistema de Cupons**: Descontos percentuais/fixos com valor mínimo
- **Autenticação**: Google OAuth 2.0 integrado
- **Notificações**: Emails via SMTP com templates HTML

## 🚀 Funcionalidades

### ✅ **E-commerce Completo**
- Carrinho de compras reativo com AJAX
- Produtos com variações (tamanho, cor, etc.)
- Checkout completo com CEP automático
- Validação de estoque em tempo real

### ✅ **Sistema de Pedidos**
- Criação automática com múltiplos itens
- Cálculo de totais com cupons de desconto
- Confirmação por email automatizada
- Redução automática de estoque

### ✅ **Cupons de Desconto**
- Descontos percentuais e fixos
- Valor mínimo para aplicação
- Validação de validade
- Interface de gerenciamento completa

### ✅ **Autenticação e Segurança**
- Google OAuth 2.0 integrado
- Proteção de rotas
- Gerenciamento de sessões

## 🛠️ Tecnologias

**Backend:** PHP 8.1+ • CodeIgniter 3.1.13 • MySQL 5.7+

**Frontend:** Bootstrap 5 • jQuery 3.6 • Font Awesome 6

**Integrações:** Google OAuth 2.0 • ViaCEP API • Gmail SMTP

## �‍♂️ Como Executar

### **1. Clone e Configure**

```bash
git clone https://github.com/lucasfflima/erp-codeigniter3.git
cd erp-codeigniter3
composer install
```

### **2. Banco de Dados** ⚠️ **OBRIGATÓRIO**

```sql
CREATE DATABASE erp_ci3 CHARACTER SET utf8 COLLATE utf8_general_ci;
```

```bash
mysql -u usuario -p erp_ci3 < erp_ci3.sql
```

**Configure obrigatoriamente** `application/config/database.php`:
```php
'hostname' => 'localhost',
'username' => 'seu_usuario',
'password' => 'sua_senha', 
'database' => 'erp_ci3',
```

### **3. Configurações Opcionais**

**Email SMTP** (`application/config/email.php`):
```php
$config['smtp_user'] = 'seu_email@gmail.com'; 
$config['smtp_pass'] = 'sua_senha_de_app';    
```

**Google OAuth** (`application/config/oauth.php`):
```php
$config['google_client_id'] = 'seu_client_id';
$config['google_client_secret'] = 'seu_client_secret';
```

### **4. Execute**

```bash
php -S localhost:8000 -t ./
```

**Acesso ao Sistema:**
- 🌐 **Principal:** http://localhost:8000 (redireciona para Google OAuth)  

## 🧪 Testes Automatizados

Execute os testes para verificar o funcionamento:

```bash
./vendor/bin/phpunit
```

**Cobertura:** 40 testes • 115 assertivas • 100% de sucesso

---

**Desenvolvido por [Lucas Lima](https://github.com/lucasfflima)**


