<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Google OAuth Configuration
|--------------------------------------------------------------------------
|
| Configurações para o Google OAuth2
| 
| IMPORTANTE: Para o OAuth funcionar, você deve registrar os URIs de 
| redirecionamento no Google Cloud Console em:
| APIs & Services > Credentials > OAuth 2.0 Client IDs
|
*/

// Configuração para desenvolvimento
$config['oauth_google_dev'] = [
    'client_id'     => '264376489091-cqcmoqggsbp3h7l2133494n6pe7vpgkt.apps.googleusercontent.com',
    'client_secret' => '4dK7WraO3aw2RwovlIrMtgUq',
];

// Configuração para produção
$config['oauth_google_prod'] = [
    'client_id'     => '264376489091-cqcmoqggsbp3h7l2133494n6pe7vpgkt.apps.googleusercontent.com',
    'client_secret' => '4dK7WraO3aw2RwovlIrMtgUq',
];

/*
|--------------------------------------------------------------------------
| URIs que devem ser registrados no Google Cloud Console:
|--------------------------------------------------------------------------
|
| Desenvolvimento:
| http://localhost/erp-codeigniter3/auth/google_callback
| http://localhost:8000/auth/google_callback (para servidor PHP built-in)
|
| Produção (substitua pelo seu domínio):
| https://seudominio.com/auth/google_callback
|
| NOTA: Adicione TODOS os URIs possíveis no Google Cloud Console para 
| evitar erros de redirect_uri não autorizado.
|
*/
