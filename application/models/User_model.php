<?php
class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database(); 
    }

    public function register($data) {
        return $this->db->insert('users', $data);
    }

    public function getByEmail($email) {
        return $this->db->get_where('users', ['email' => $email])->row();
    }

    public function getById($id) {
        return $this->db->get_where('users', ['id' => $id])->row();
    }
}
