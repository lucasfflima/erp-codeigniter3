<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Email Configuration
| -------------------------------------------------------------------------
| Configuração para envio de e-mails do sistema
*/

// Configuração SMTP (Gmail) com debug ativado
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.gmail.com';
$config['smtp_port'] = 587;
$config['smtp_crypto'] = 'tls';
$config['smtp_user'] = 'seuemail@teste.com'; 
$config['smtp_pass'] = 'sua_senha_aqui';       
$config['smtp_timeout'] = 30;
$config['smtp_keepalive'] = FALSE;

// Configurações gerais
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
$config['wordwrap'] = TRUE;
$config['validate'] = TRUE;

// Configurações do remetente
$config['from_email'] = 'seuemail@teste.com';
$config['from_name'] = 'ERP Sistema';

// Debug desabilitado para produção
$config['smtp_debug'] = 0; // 0 = off, 1 = client, 2 = client and server
