<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Busca usuário pelo google_id ou email
    public function findOrCreateFromGoogle($userData) {
        // Tenta achar pelo google_id
        $this->db->where('google_id', $userData['id']);
        $user = $this->db->get('usuarios')->row_array();

        if (!$user) {
            // Se não achar, tenta pelo email
            $this->db->where('email', $userData['email']);
            $user = $this->db->get('usuarios')->row_array();
        }

        if ($user) {
            // Atualiza os dados (nome, foto) se mudou
            $this->db->where('id', $user['id']);
            $this->db->update('usuarios', [
                'nome' => $userData['name'],
                'foto' => $userData['picture'] ?? null,
                'email' => $userData['email'],
                'google_id' => $userData['id']
            ]);
            return $this->getById($user['id']);
        } else {
            // Cria usuário novo
            $this->db->insert('usuarios', [
                'nome' => $userData['name'],
                'email' => $userData['email'],
                'google_id' => $userData['id'],
                'foto' => $userData['picture'] ?? null
            ]);
            $id = $this->db->insert_id();
            return $this->getById($id);
        }
    }

    public function getById($id) {
        return $this->db->get_where('usuarios', ['id' => $id])->row_array();
    }
}
