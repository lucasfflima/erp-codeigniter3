<?php
use League\OAuth2\Client\Provider\Google;

class Googleoauth {
    protected $provider;
    protected $ci;

    public function __construct() {
        $this->ci =& get_instance();
        $this->ci->load->config('oauth');
        
        // Configuração para diferentes ambientes
        $config = $this->getOAuthConfig();
        
        $this->provider = new Google([
            'clientId'     => $config['client_id'],
            'clientSecret' => $config['client_secret'],
            'redirectUri'  => $config['redirect_uri'],
        ]);
    }

    /**
     * Obtém a configuração do OAuth baseada no ambiente
     */
    private function getOAuthConfig() {
        // Detecta se está em desenvolvimento ou produção
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $is_development = (
            ENVIRONMENT === 'development' || 
            strpos($host, 'localhost') !== false || 
            strpos($host, '127.0.0.1') !== false ||
            strpos($host, '.local') !== false
        );
        
        if ($is_development) {
            $oauth_config = $this->ci->config->item('oauth_google_dev');
        } else {
            $oauth_config = $this->ci->config->item('oauth_google_prod');
        }
        
        return [
            'client_id'     => $oauth_config['client_id'],
            'client_secret' => $oauth_config['client_secret'],
            'redirect_uri'  => base_url('auth/google_callback'),
        ];
    }

    public function getAuthorizationUrl() {
        return $this->provider->getAuthorizationUrl();
    }

    public function getState() {
        return $this->provider->getState();
    }

    public function getAccessToken($code) {
        return $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);
    }

    public function getResourceOwner($token) {
        return $this->provider->getResourceOwner($token);
    }
}
