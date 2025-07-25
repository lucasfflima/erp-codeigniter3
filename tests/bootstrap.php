<?php
define('ENVIRONMENT', 'testing');

define('BASEPATH', realpath(__DIR__ . '/../system') . DIRECTORY_SEPARATOR);
define('APPPATH', realpath(__DIR__ . '/../application') . DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH . 'views' . DIRECTORY_SEPARATOR);

// Autoload do Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Configurar variáveis de servidor
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Carregar as funções comuns do CI
require_once BASEPATH . 'core/Common.php';

// Incluir as classes do banco de dados
require_once BASEPATH . 'database/DB.php';

// Carregar a configuração
$config = array();
require APPPATH . 'config/config.php';

// Carregar configuração do banco
$db = array();
$active_group = 'default';
$query_builder = TRUE;
require APPPATH . 'config/database.php';

// Conectar ao banco de teste
$CI = new stdClass();
$CI->db = DB($db['testing'], $query_builder);

// Carregar o loader
require_once BASEPATH . 'core/Loader.php';
$CI->load = new CI_Loader();
$CI->load->initialize();

// Definir a instância global do CI
function &get_instance() {
    global $CI;
    return $CI;
}